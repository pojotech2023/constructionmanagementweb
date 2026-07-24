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

    public const MENU_VISIBILITY_KEY = 'sidebar_menu_visibility';

    public const MENU_ITEMS = [
        'site_management' => 'Site Management',
        'generate_quotation' => 'Generate Quotation',
        'customer_management' => 'Customer Management',
        'vendor' => 'Vendor',
        'subcontractor' => 'SubContractor',
        'supervisor_creation' => 'Supervisor Creation',
        'property_list' => 'Property List',
        'site_detail' => 'Site Detail',
        'export' => 'Export (Site Detail Report)',
        'attendance' => 'Today Attendance',
        'materials' => 'Materials',
        'subcontractor_detail' => 'SubContractor (Site Detail)',
        'payment_status' => 'Payment Status',
        'checklist' => 'Check List',
        'tickets' => 'Tickets',
        'drawing' => 'Drawing',
        'sales_bill' => 'Sales Bill',
        'purchase_bill' => 'Purchase Bill',
        'material_estimation' => 'Material Estimation Request',
        'unit_master' => 'Unit Master',
    ];

    // Sub-items nested under a parent menu on the Admin Control screen.
    public const MENU_CHILDREN = [
        'site_management' => [
            'site_detail', 'export', 'attendance', 'materials',
            'subcontractor_detail', 'payment_status', 'checklist', 'tickets', 'drawing', 'sales_bill', 'purchase_bill', 'material_estimation',
        ],
    ];

    public static function getMenuGroups(): array
    {
        $childKeys = array_merge(...array_values(self::MENU_CHILDREN));

        $groups = [];
        foreach (self::MENU_ITEMS as $key => $label) {
            if (in_array($key, $childKeys, true)) {
                continue;
            }

            $groups[$key] = [
                'label' => $label,
                'children' => array_map(
                    fn ($childKey) => ['key' => $childKey, 'label' => self::MENU_ITEMS[$childKey]],
                    self::MENU_CHILDREN[$key] ?? []
                ),
            ];
        }

        return $groups;
    }

    public static function getMenuVisibility(): array
    {
        $stored = json_decode((string) self::getValue(self::MENU_VISIBILITY_KEY, '{}'), true) ?: [];

        $visibility = [];
        foreach (array_keys(self::MENU_ITEMS) as $key) {
            $visibility[$key] = array_key_exists($key, $stored) ? (bool) $stored[$key] : true;
        }

        return $visibility;
    }

    public static function setMenuVisibility(array $visibility): void
    {
        $data = [];
        foreach (array_keys(self::MENU_ITEMS) as $key) {
            $data[$key] = !empty($visibility[$key]);
        }

        self::setValue(self::MENU_VISIBILITY_KEY, json_encode($data));
    }

    public const HIDDEN_MATERIAL_TYPES_KEY = 'hidden_material_types';
    public const HIDDEN_SUBCONTRACTOR_TYPES_KEY = 'hidden_subcontractor_types';

    public static function getHiddenMaterialTypes(): array
    {
        return json_decode((string) self::getValue(self::HIDDEN_MATERIAL_TYPES_KEY, '[]'), true) ?: [];
    }

    public static function hideMaterialType(string $slug): void
    {
        $hidden = self::getHiddenMaterialTypes();
        if (!in_array($slug, $hidden, true)) {
            $hidden[] = $slug;
        }
        self::setValue(self::HIDDEN_MATERIAL_TYPES_KEY, json_encode($hidden));
    }

    public static function getHiddenSubcontractorTypes(): array
    {
        return json_decode((string) self::getValue(self::HIDDEN_SUBCONTRACTOR_TYPES_KEY, '[]'), true) ?: [];
    }

    public static function hideSubcontractorType(string $slug): void
    {
        $hidden = self::getHiddenSubcontractorTypes();
        if (!in_array($slug, $hidden, true)) {
            $hidden[] = $slug;
        }
        self::setValue(self::HIDDEN_SUBCONTRACTOR_TYPES_KEY, json_encode($hidden));
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
