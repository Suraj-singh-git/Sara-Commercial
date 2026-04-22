<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class WebsiteSetting extends Model
{
    public const DEFAULT_SITE_TITLE = 'Sara Commercial — Industrial Supplies & Equipment';
    public const DEFAULT_COMPANY_NAME = 'Sara Commercial';
    public const DEFAULT_THEME_COLOR = '#065f46';

    protected $fillable = [
        'site_title',
        'company_name',
        'theme_color',
        'logo_path',
    ];

    /**
     * @return array{site_title: string, company_name: string, theme_color: string, logo_path: ?string}
     */
    public static function defaults(): array
    {
        return [
            'site_title' => self::DEFAULT_SITE_TITLE,
            'company_name' => self::DEFAULT_COMPANY_NAME,
            'theme_color' => self::DEFAULT_THEME_COLOR,
            'logo_path' => null,
        ];
    }

    public static function current(): self
    {
        if (! Schema::hasTable('website_settings')) {
            $model = new self();
            $model->forceFill(self::defaults());

            return $model;
        }

        $setting = self::query()->first();
        if ($setting) {
            return $setting;
        }

        return self::query()->create(self::defaults());
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->logo_path, '/'));
    }
}

