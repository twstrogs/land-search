<?php

/**
 * Helper functions for views
 */

if (!function_exists('_hasActiveFilters')) {
    function _hasActiveFilters($request) {
        return $request->get('property_type') || 
               $request->get('price_min') || 
               $request->get('price_max') ||
               $request->get('area_min') ||
               $request->get('area_max') ||
               $request->get('direction') ||
               $request->get('legal') ||
               $request->get('mat_tien') ||
               $request->get('ngo_o_to') ||
               $request->get('gan_truong') ||
               $request->get('gan_kcn');
    }
}

if (!function_exists('_removeFilter')) {
    function _removeFilter($request, ...$params) {
        $query = $request->except($params);
        return route('search', $query);
    }
}

if (!function_exists('_formatPrice')) {
    function _formatPrice($value) {
        if (!$value) return '';
        $value = floatval($value);
        if ($value >= 1000) {
            return number_format($value / 1000, 1) . ' tỷ';
        }
        return number_format($value) . ' triệu';
    }
}
