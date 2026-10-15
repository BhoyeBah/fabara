<?php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\PrintOrder;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\Sale;
use App\Models\User;
use App\Support\Checkout;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FabaraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();   // sous-menus + comptes de départ
    }

    private function product(array $o = []): Product
    {
        return Product::create($o + ['category_id' => Category::first()->id, 'name' => 'Test', 'price' => 100, 'cost_price' => 60, 'stock' => 10, 'min_stock' => 3]);
    }

    private function user(string $who): User
    {
        $emails = [
            'admin' => 'fabara@gmail.com',
            'gerante' => 'aissatou@gmail.com',
            'agent1' => 'biro@gmail.com',
        ];

        return User::where('email', $emails[$who] ?? $who . '@fabara.local')->firstOrFail();
    }

    // ---------- site public ----------
    public function test_home_page_shows_both_menus_without_fake_products(): void
    {
        $this->get('/')->assertOk()->assertSee('Cartes de visite')->assertSee('Tunique MakiSall')->assertSee('Bientôt disponible');
        $this->assertDatabaseCount('products', 0);   // aucun article d'exemple
    }

    public function test_category_and_product_pages(): void
    {
        $c = Category::where('slug', 'sacs')->first();
        $visible = $this->product(['category_id' => $c->id, 'name' => 'Visible']);
        $this->product(['category_id' => $c->id, 'name' => 'Invisible', 'is_active' => false]);
        $this->get('/boutique/sacs')->assertOk()->assertSee('Visible')->assertDontSee('Invisible');
        $this->get('/boutique/sacs?q=Vis')->assertOk()->assertSee('Visible');
        $this->get('/imprimerie/sacs')->assertNotFound();
        $this->get('/article/' . $visible->id)->assertOk()->assertSee('Visible');
        $hidden = Product::where('name', 'Invisible')->first();
        $this->get('/article/' . $hidden->id)->assertNotFound();
        $this->get('/')->assertOk()->assertSee('Derniers articles');
    }

    // ---------- connexion et droits ----------
    public function test_login_redirects_each_role_to_its_interface(): void
    {
        $this->post('/connexion', ['email' => 'fabara@gmail.com', 'password' => 'Abcde150'])->assertRedirect('/admin');
        auth()->logout();
        $this->post('/connexion', ['email' => 'aissatou@gmail.com', 'password' => 'Abcde150'])->assertRedirect('/caisse');
        auth()->logout();
        $this->post('/connexion', ['email' => 'biro@gmail.com', 'password' => 'Abcde150'])->assertRedirect('/atelier');
    }

    public function test_wrong_password_and_inactive_account_are_refused(): void
    {
        $this->post('/connexion', ['email' => 'fabara@gmail.com', 'password' => 'faux'])->assertSessionHasErrors('email');
        User::where('email', 'biro@gmail.com')->update(['is_active' => false]);
        $this->post('/connexion', ['email' => 'biro@gmail.com', 'password' => 'Abcde150'])->assertSessionHasErrors('email');
    }

    public function test_roles_cannot_open_other_interfaces(): void
    {
        $this->actingAs($this->user('agent1'))->get('/admin')->assertForbidden();
        $this->actingAs($this->user('agent1'))->get('/caisse')->assertForbidden();
        $this->actingAs($this->user('gerante'))->get('/atelier')->assertForbidden();
        $this->actingAs($this->user('gerante'))->get('/admin')->assertForbidden();
    }

    // ---------- chaque page de chaque interface s'ouvre (détecte les erreurs 500) ----------
    public function test_every_admin_page_opens(): void
    {
        $this->actingAs($this->user('admin'));
        foreach (['', '/pos', '/products', '/categories', '/customers', '/expenses', '/users', '/suppliers', '/purchases', '/sales',
                  '/print-orders', '/quote-requests', '/stock-movements', '/accounting', '/settings'] as $p) {
            $this->get('/admin' . $p)->assertOk();
        }
    }

    public function test_every_caisse_page_opens_and_admin_only_pages_do_not_exist(): void
    {
        $this->actingAs($this->user('gerante'));
        foreach (['', '/pos', '/sales', '/products', '/customers', '/quote-requests'] as $p) {
            $this->get('/caisse' . $p)->assertOk();
        }
        foreach (['/stock-movements', '/accounting', '/settings', '/users', '/expenses'] as $p) {
            $this->get('/caisse' . $p)->assertNotFound();
        }
    }

    public function test_every_atelier_page_opens(): void
    {
        $this->actingAs($this->user('agent1'));
        foreach (['', '/print-orders', '/quote-requests', '/customers'] as $p) {
            $this->get('/atelier' . $p)->assertOk();
        }
        $this->get('/atelier/sales')->assertNotFound();
        $this->get('/atelier/products')->assertNotFound();
    }

    public function test_invoice_receipt_and_exports_need_the_right_login(): void
    {
        $sale = Sale::create(['customer_name' => 'X']);
        $this->get('/factures/' . $sale->id)->assertRedirect(route('login'));
        $this->get('/recus/' . $sale->id)->assertRedirect(route('login'));
        $this->actingAs($this->user('gerante'))->get('/export/ventes')->assertForbidden();
        $this->actingAs($this->user('admin'))->get('/export/ventes')->assertOk();
    }

    // ---------- vente : prix du catalogue, stock ----------
    public function test_checkout_uses_catalog_price_and_updates_stock(): void
    {
        $p = $this->product(['price' => 100, 'stock' => 3]);
        $sale = Checkout::make([$p->id => 2], 'Awa', 'especes', null, 0, null);
        $this->assertEquals(200, $sale->total);
        $this->assertEquals(0, $sale->balance);
        $this->assertEquals(1, $p->fresh()->stock);
        $this->assertEquals(100, $sale->items->first()->unit_price);
    }

    public function test_checkout_blocks_oversell_and_missing_price(): void
    {
        $p = $this->product(['stock' => 3]);
        try {
            Checkout::make([$p->id => 5]);
            $this->fail('La vente aurait dû être refusée (stock).');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Stock insuffisant', $e->getMessage());
        }
        $noPrice = $this->product(['name' => 'Sans prix', 'price' => null]);
        $this->expectException(DomainException::class);
        Checkout::make([$noPrice->id => 1]);
    }

    public function test_sale_totals_and_cancel_restores_stock(): void
    {
        $p = $this->product();   // stock 10 (1 mouvement « stock initial »)
        $sale = Checkout::make([$p->id => 2], 'X', 'especes', 50, 10, null);
        $this->assertEquals(190, $sale->total);
        $this->assertEquals(140, $sale->balance);
        $this->assertEquals(8, $p->fresh()->stock);

        $sale->cancel('erreur');
        $this->assertEquals(10, $p->fresh()->stock);
        $this->assertNotNull($sale->fresh()->cancelled_at);
        $this->assertEquals(0, Sale::active()->count());
        $this->assertDatabaseCount('stock_movements', 3);   // initial + vente + retour
    }

    public function test_unlimited_stock_is_never_changed(): void
    {
        $p = $this->product(['stock' => null]);
        Checkout::make([$p->id => 5]);
        $this->assertNull($p->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    // ---------- commandes d'imprimerie ----------
    public function test_print_order_price_comes_from_catalog_and_goes_through_statuses(): void
    {
        $c = Category::where('slug', 'cartes-de-visite')->first();
        $cat = $this->product(['category_id' => $c->id, 'name' => 'Cartes x100', 'price' => 300, 'stock' => null]);
        $o = PrintOrder::create(['customer_name' => 'Awa', 'phone' => '620 11 22 33', 'title' => 'Cartes', 'product_id' => $cat->id, 'quantity' => 2, 'deposit' => 200, 'material_cost' => 120]);
        $this->assertNotEmpty($o->code);
        $this->assertEquals(600, $o->fresh()->price);
        $o->advance();
        $o->advance();
        $this->assertEquals('prete', $o->fresh()->status->value);
        $sale = $o->fresh()->deliver();
        $this->assertEquals(600, $sale->fresh()->total);
        $this->assertEquals(400, $sale->fresh()->balance);
        $this->assertEquals('livree', $o->fresh()->status->value);
    }

    public function test_client_request_and_tracking(): void
    {
        $this->post('/demande', ['name' => 'Awa', 'phone' => '620112233', 'subject' => '100 cartes de visite'])->assertRedirect();
        $req = QuoteRequest::first();
        $this->assertEquals('nouvelle', $req->status);
        $order = $req->toPrintOrder();
        $this->assertEquals('traitee', $req->fresh()->status);
        $this->post('/suivi', ['code' => $order->code, 'phone' => '620112233'])->assertOk()->assertSee('100 cartes de visite');
        $this->post('/suivi', ['code' => $order->code, 'phone' => '699999999'])->assertSee('Aucune commande trouvée');
    }

    public function test_quote_form_honeypot_blocks_robots(): void
    {
        $this->post('/demande', ['name' => 'Bot', 'phone' => '1', 'subject' => 'spam', 'website' => 'http://x'])->assertRedirect();
        $this->assertDatabaseCount('quote_requests', 0);
    }

    // ---------- API ----------
    public function test_api_sale_ignores_client_prices_and_gerante_discount(): void
    {
        $p = $this->product(['price' => 100, 'stock' => 10]);
        $token = $this->postJson('/api/login', ['email' => 'aissatou@gmail.com', 'password' => 'Abcde150'])->assertOk()->json('token');
        $r = $this->withToken($token)->postJson('/api/sales', [
            'discount' => 50, 'items' => [['product_id' => $p->id, 'quantity' => 2, 'unit_price' => 1]],
        ])->assertCreated();
        $this->assertEquals(200, $r->json('total'));      // prix du catalogue, pas 1
        $this->assertEquals(0, $r->json('discount'));     // la gérante n'a pas de remise
        $this->withToken($token)->getJson('/api/print-orders')->assertForbidden();
        $this->getJson('/api/categories')->assertOk();
    }

    // ---------- photos ----------
    public function test_uploaded_photos_are_served_by_the_media_route(): void
    {
        $path = 'products/test-' . uniqid() . '.png';
        Storage::disk('public')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
        try {
            $p = $this->product(['image' => $path]);
            $this->assertEquals('/media/' . $path, $p->image_url);
            $this->get($p->image_url)->assertOk();
            $this->get('/media/products/introuvable.png')->assertNotFound();
        } finally {
            Storage::disk('public')->delete($path);
        }
    }

    public function test_search_page(): void
    {
        $this->product(['name' => 'Robe rouge']);
        $this->get('/recherche?q=Robe')->assertOk()->assertSee('Robe rouge');
        $this->get('/recherche?q=zzzzzz')->assertOk()->assertSee('Aucun résultat');
        $this->get('/recherche')->assertOk();
    }

    // ---------- données de démonstration ----------
    public function test_demo_data_fills_everything_and_can_be_cleared(): void
    {
        $this->artisan('fabara:demo')->assertSuccessful();
        foreach (Category::all() as $c) {
            $this->assertEquals(5, $c->products()->count(), 'Il faut 5 articles dans « ' . $c->name . ' »');
        }
        $this->assertGreaterThan(10, Sale::count());
        $this->assertGreaterThan(8, PrintOrder::count());
        $this->assertGreaterThan(4, QuoteRequest::count());
        $this->assertEquals(0, Product::whereNotNull('stock')->where('stock', '<', 0)->count());

        // Le suivi fonctionne avec les codes de démonstration
        $this->post('/suivi', ['code' => 'DEMO01', 'phone' => '620 11 22 01'])->assertOk()->assertSee('Cartes de visite x500');
        $this->get('/')->assertOk()->assertSee('Derniers articles');
        $this->get('/boutique/chaussures')->assertOk()->assertSee('Baskets homme');
        $this->actingAs($this->user('admin'));
        foreach (['', '/sales', '/products', '/print-orders', '/quote-requests', '/purchases', '/expenses', '/customers', '/stock-movements', '/accounting'] as $p) {
            $this->get('/admin' . $p)->assertOk();
        }

        $this->artisan('fabara:clear-demo --force')->assertSuccessful();
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('print_orders', 0);
        $this->assertDatabaseCount('quote_requests', 0);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_clear_demo_keeps_real_data(): void
    {
        $mine = $this->product(['name' => 'Mon vrai article']);
        $this->artisan('fabara:demo')->assertSuccessful();
        $this->artisan('fabara:clear-demo --force')->assertSuccessful();
        $this->assertDatabaseHas('products', ['id' => $mine->id]);
    }

    // ---------- accueils simplifiés ----------
    public function test_each_interface_home_shows_its_big_buttons(): void
    {
        $this->artisan('fabara:demo')->assertSuccessful();
        $this->actingAs($this->user('admin'))->get('/admin')->assertOk()->assertSee('Nouvelle vente')->assertSee('Dernières ventes')->assertSee('Stock bas');
        $this->flushSession();
        $this->actingAs($this->user('gerante'))->get('/caisse')->assertOk()->assertSee('Nouvelle vente')->assertSee('Dernières ventes');
        $this->get('/caisse/pos')->assertOk()->assertSee('Valider et générer le reçu');
        $this->flushSession();
        $this->actingAs($this->user('agent1'))->get('/atelier')->assertOk()->assertSee('À commencer')->assertSee('Prêtes à remettre')->assertSee('DEMO01');
        $this->flushSession();
        $this->actingAs($this->user('admin'))->get('/admin/pos')->assertOk()->assertSee('Remise');
    }

    public function test_agent_moves_an_order_forward_from_the_board(): void
    {
        $agent = $this->user('agent1');
        $order = PrintOrder::create(['customer_name' => 'Test', 'phone' => '620000000', 'title' => 'Flyers', 'price' => 50000]);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('atelier'));
        $this->actingAs($agent);
        Livewire::test(\App\Filament\Pages\Dashboard::class)->call('advance', $order->id);
        $this->assertEquals('en_cours', $order->fresh()->status->value);
        Livewire::test(\App\Filament\Pages\Dashboard::class)->call('advance', $order->id);
        $this->assertEquals('prete', $order->fresh()->status->value);
        Livewire::test(\App\Filament\Pages\Dashboard::class)->call('deliver', $order->id);
        $this->assertEquals('livree', $order->fresh()->status->value);
        $this->assertNotNull($order->fresh()->sale_id);
    }

    // ---------- graphiques des tableaux de bord ----------
    public function test_dashboard_charts_render_for_each_role_with_demo_data(): void
    {
        $this->artisan('fabara:demo')->assertSuccessful();
        $byRole = [
            'admin' => [\App\Filament\Widgets\SalesTrend::class, \App\Filament\Widgets\SalesByMenu::class, \App\Filament\Widgets\PaymentMethods::class,
                        \App\Filament\Widgets\TopProducts::class, \App\Filament\Widgets\OrdersByStatus::class, \App\Filament\Widgets\ResultByMonth::class,
                        \App\Filament\Widgets\OrdersDue::class],
            'gerante' => [\App\Filament\Widgets\SalesTrend::class, \App\Filament\Widgets\TopProducts::class],
            'agent' => [\App\Filament\Widgets\OrdersByStatus::class, \App\Filament\Widgets\OrdersDue::class],
        ];
        foreach ($byRole as $role => $widgets) {
            $this->flushSession();
            $this->actingAs($this->user($role === 'agent' ? 'agent1' : $role));
            foreach ($widgets as $w) {
                $this->assertTrue($w::canView(), "$role doit voir $w");
                Livewire::test($w)->assertOk();
            }
            $this->get(['admin' => '/admin', 'gerante' => '/caisse', 'agent' => '/atelier'][$role])->assertOk();
        }
        // Le code JavaScript des graphiques est placé dans un attribut HTML : pas de guillemets doubles
        // (avec une devise ou sans, sinon la page affiche du code au lieu du graphique).
        foreach (['', 'GNF'] as $currency) {
            \App\Support\Setting::put('currency', $currency);
            foreach ($byRole['admin'] as $w) {
                $widget = new $w();
                $method = new \ReflectionMethod($widget, 'getOptions');
                $method->setAccessible(true);
                $options = $method->invoke($widget);
                if ($options instanceof \Filament\Support\RawJs) {
                    $this->assertStringNotContainsString('"', (string) $options->toHtml(), "$w contient un guillemet double");
                }
            }
        }
        // Chacun ne voit que ses graphiques
        $this->actingAs($this->user('agent1'));
        $this->assertFalse(\App\Filament\Widgets\SalesTrend::canView());
        $this->assertFalse(\App\Filament\Widgets\ResultByMonth::canView());
        $this->actingAs($this->user('gerante'));
        $this->assertFalse(\App\Filament\Widgets\ResultByMonth::canView());
        $this->assertFalse(\App\Filament\Widgets\OrdersByStatus::canView());
    }

    // ---------- comptes clients, conformité, changement de compte ----------
    private function registerClient(string $email = 'awa@test.com', string $phone = '620 11 22 33'): User
    {
        $this->post('/inscription', ['name' => 'Awa Test', 'email' => $email, 'phone' => $phone, 'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1', 'terms' => '1'])->assertRedirect('/mon-compte');
        return User::where('email', $email)->firstOrFail();
    }

    public function test_client_can_register_only_with_consent_and_gets_client_role(): void
    {
        $this->post('/inscription', ['name' => 'X', 'email' => 'x@test.com', 'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1'])->assertSessionHasErrors('terms');
        $this->assertDatabaseMissing('users', ['email' => 'x@test.com']);
        $u = $this->registerClient();
        $this->assertSame('client', $u->role->value);
        $this->assertNotNull($u->terms_accepted_at);
        $this->get('/mon-compte')->assertOk()->assertSee('Supprimer mon compte');
    }

    public function test_client_cannot_open_any_staff_interface(): void
    {
        $this->registerClient();
        foreach (['/admin', '/caisse', '/atelier'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->get('/factures/1')->assertStatus(403);
    }

    public function test_unified_login_sends_client_to_account_and_old_url_redirects(): void
    {
        $this->registerClient();
        auth()->logout();
        $this->flushSession();
        $this->post('/connexion', ['email' => 'awa@test.com', 'password' => 'motdepasse1'])->assertRedirect('/mon-compte');
        $this->get('/equipe')->assertRedirect('/connexion');
        $this->get('/admin/login')->assertRedirect('/connexion');
    }

    public function test_logged_in_user_sees_continue_or_switch_account_and_admin_can_open_every_dashboard(): void
    {
        $this->actingAs($this->user('admin'));
        $this->get('/connexion')->assertOk()->assertSee('Changer de compte');
        foreach (['/admin', '/caisse', '/atelier'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_client_export_and_deletion_erases_personal_data_but_keeps_invoices_anonymous(): void
    {
        $u = $this->registerClient();
        \App\Models\QuoteRequest::create(['name' => 'Awa', 'phone' => '+224 620 11 22 33', 'subject' => 'Flyers']);
        $cust = \App\Models\Customer::create(['name' => 'Awa', 'phone' => '620112233']);
        $sale = Sale::create(['customer_id' => $cust->id, 'customer_name' => 'Awa']);
        $this->get('/mon-compte')->assertSee('Flyers');
        $this->get('/mon-compte/donnees.json')->assertOk()->assertJsonPath('compte.email', 'awa@test.com')->assertJsonPath('demandes_de_devis.0.objet', 'Flyers');

        $this->delete('/mon-compte', ['password' => 'faux', 'confirm' => '1'])->assertSessionHasErrors('password');
        $this->delete('/mon-compte', ['password' => 'motdepasse1', 'confirm' => '1'])->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['email' => 'awa@test.com']);
        $this->assertDatabaseCount('quote_requests', 0);
        $this->assertDatabaseCount('customers', 0);
        $this->assertSame('Client supprimé', $sale->fresh()->customer_name);
        $this->get('/mon-compte')->assertRedirect('/connexion');
    }

    public function test_staff_cannot_use_client_deletion_and_clients_are_hidden_from_team_list(): void
    {
        $this->actingAs($this->user('admin'));
        $this->delete('/mon-compte', ['password' => 'Abcde150', 'confirm' => '1'])->assertForbidden();
        User::create(['name' => 'Cli', 'email' => 'cli@test.com', 'password' => 'motdepasse1', 'role' => 'client', 'is_active' => true]);
        $this->assertSame(0, \App\Filament\Resources\UserResource::getEloquentQuery()->where('email', 'cli@test.com')->count());
    }

    public function test_compliance_pages_and_footer_links_exist(): void
    {
        $this->get('/confidentialite')->assertOk()->assertSee('Politique de confidentialité');
        $this->get('/conditions')->assertOk()->assertSee('Conditions d’utilisation');
        $this->get('/suppression-compte')->assertOk()->assertSee('Supprimer mon compte');
        $this->post('/suppression-compte', ['email' => 'a@b.com'])->assertSessionHas('status');
        $this->get('/')->assertSee('/confidentialite', false)->assertSee('Créer un compte')->assertDontSee('Espace équipe');
    }

    public function test_api_account_deletion_for_clients(): void
    {
        $u = User::create(['name' => 'Api', 'email' => 'api@test.com', 'password' => 'motdepasse1', 'role' => 'client', 'is_active' => true]);
        $token = $this->postJson('/api/login', ['email' => 'api@test.com', 'password' => 'motdepasse1'])->assertOk()->json('token');
        $h = ['Authorization' => 'Bearer ' . $token];
        $this->getJson('/api/account/export', $h)->assertOk()->assertJsonPath('compte.email', 'api@test.com');
        $this->getJson('/api/sales', $h)->assertForbidden();
        $this->deleteJson('/api/account', ['password' => 'motdepasse1'], $h)->assertOk();
        $this->assertDatabaseMissing('users', ['email' => 'api@test.com']);
    }

    public function test_catalog_importer_reads_csv_with_photos(): void
    {
        Storage::fake('public');
        $dir = sys_get_temp_dir() . '/imp' . uniqid();
        mkdir($dir . '/images', 0775, true);
        file_put_contents($dir . '/images/a.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        file_put_contents($dir . '/a.csv', "sous_menu;nom;prix;stock;cout;description;image\nSacs;Sac rouge;150 000;5;100000;Joli;a.png\nInconnu;X;1;;;;\n");
        $imp = (new \App\Support\CatalogImporter())->run($dir . '/a.csv', $dir . '/images');
        $this->assertSame(1, $imp->created);
        $this->assertCount(1, $imp->errors);
        $p = Product::where('name', 'Sac rouge')->first();
        $this->assertEquals(150000, $p->price);
        $this->assertNotNull($p->image);
    }

    public function test_gerante_can_open_receipt_and_import_xlsx_reader_works(): void
    {
        $p = $this->product(['price' => 1000, 'stock' => 5, 'name' => 'Sac test']);
        $this->actingAs($this->user('gerante'));
        $sale = Checkout::make([$p->id => 2], 'Client comptoir', 'especes', null, 0, auth()->id());
        $this->get(route('receipt', $sale))->assertOk()->assertSee($sale->number)->assertSee('Sac test')->assertSee('Imprimer');

        // lecture d'un vrai fichier .xlsx minimal
        $f = tempnam(sys_get_temp_dir(), 'x') . '.xlsx';
        $z = new \ZipArchive();
        $z->open($f, \ZipArchive::CREATE);
        $z->addFromString('xl/sharedStrings.xml', '<sst><si><t>nom</t></si><si><t>prix</t></si><si><t>Sac</t></si></sst>');
        $z->addFromString('xl/worksheets/sheet1.xml', '<worksheet><sheetData><row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row><row r="2"><c r="A2" t="s"><v>2</v></c><c r="C2"><v>5000</v></c></row></sheetData></worksheet>');
        $z->close();
        $rows = \App\Support\TableReader::read($f);
        $this->assertSame(['nom', 'prix'], $rows[0]);
        $this->assertSame(['Sac', '', '5000'], $rows[1]);
    }

    public function test_pos_total_is_computed_and_sale_gives_receipt(): void
    {
        $p = $this->product(['price' => 2500, 'stock' => 9, 'name' => 'Sac caisse']);
        $this->actingAs($this->user('gerante'));
        $c = Livewire::test(\App\Filament\Pages\Pos::class)
            ->assertSee('Valider et générer le reçu')
            ->call('add', $p->id)->call('add', $p->id)
            ->assertSee('5 000')
            ->call('complete')
            ->assertSee('Imprimer le reçu')
            ->call('newSale')
            ->assertDontSee('Imprimer le reçu');
        $this->assertSame(7, Product::find($p->id)->stock);
    }
}
