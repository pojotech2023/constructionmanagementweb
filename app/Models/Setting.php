<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEFAULT_PLAN_KEY = 'basic';

    public const PLAN_DEFINITIONS = [
        'basic' => ['name' => 'Basic', 'site_limit' => 5],
        'starter' => ['name' => 'Starter', 'site_limit' => 10],
        'advanced' => ['name' => 'Advanced', 'site_limit' => 15],
        'advanced_plus' => ['name' => 'Advanced Plus', 'site_limit' => 20],
        'professional' => ['name' => 'Professional', 'site_limit' => 25],
        'business' => ['name' => 'Business', 'site_limit' => 30],
        'business_elite' => ['name' => 'Business Elite', 'site_limit' => 35],
    ];

    public static function getValue($key, $default = null)
    {
        try {
            $setting = self::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            return $setting->value;
        } catch (\Illuminate\Database\QueryException $e) {
            // Settings table may not exist yet (migrations not run)
            return $default;
        }
    }

    public static function setValue($key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function normalizePlanKey(?string $planKey): string
    {
        $planKey = strtolower(trim((string) $planKey));

        return array_key_exists($planKey, self::PLAN_DEFINITIONS)
            ? $planKey
            : self::DEFAULT_PLAN_KEY;
    }

    public static function getPlanDefinitions(): array
    {
        return self::PLAN_DEFINITIONS;
    }

    public static function getCurrentPlan(): array
    {
        $storedPlanKey = self::getValue('plan_name', self::DEFAULT_PLAN_KEY);
        $planKey = self::normalizePlanKey($storedPlanKey);
        $plan = self::PLAN_DEFINITIONS[$planKey];

        $storedSiteLimit = self::getValue('site_limit', (string) $plan['site_limit']);
        $siteLimit = is_numeric($storedSiteLimit) && (int) $storedSiteLimit > 0
            ? (int) $storedSiteLimit
            : (int) $plan['site_limit'];

        if ($siteLimit !== (int) $plan['site_limit']) {
            foreach (self::PLAN_DEFINITIONS as $key => $definition) {
                if ((int) $definition['site_limit'] === $siteLimit) {
                    $planKey = $key;
                    $plan = $definition;
                    break;
                }
            }
        }

        return [
            'key' => $planKey,
            'name' => $plan['name'],
            'site_limit' => (int) $plan['site_limit'],
        ];
    }
}
