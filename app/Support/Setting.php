<?php
namespace App\Support;

use App\Models\Setting as Row;
use Throwable;

/** Réglages modifiables dans l'admin (table settings), avec repli sur config/fabara.php et .env. */
class Setting
{
    private static ?array $cache = null;

    public const DEFAULTS = [
        'company_name' => 'Fabara', 'phone' => '', 'address' => '', 'invoice_footer' => 'Merci de votre confiance.',
    ];

    private static function load(): array
    {
        if (self::$cache === null) {
            try {
                self::$cache = Row::pluck('value', 'key')->all();
            } catch (Throwable) {
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $v = self::load()[$key] ?? null;
        if ($v !== null && $v !== '') {
            return $v;
        }
        return $default ?? (self::DEFAULTS[$key] ?? config('fabara.' . $key));
    }

    public static function put(string $key, mixed $value): void
    {
        Row::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache = null;
    }

    public static function all(): array
    {
        return [
            'company_name' => self::get('company_name'), 'phone' => self::get('phone'), 'address' => self::get('address'),
            'whatsapp' => self::get('whatsapp'), 'currency' => self::get('currency'), 'tax_rate' => self::get('tax_rate'),
            'invoice_footer' => self::get('invoice_footer'),
        ];
    }

    public static function whatsapp(): string
    {
        return preg_replace('/\D/', '', (string) self::get('whatsapp'));
    }
}
