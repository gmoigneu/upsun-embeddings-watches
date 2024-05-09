<?php
namespace App\Helpers;
use App\Models\Watch;

class MarkdownHelper
{
    public static function toMarkdown(Watch $watch): string
    {
        return <<<MARKDOWN
            **{$watch->brand} {$watch->model}**
            - Case Material: {$watch->case_material}
            - Strap Material: {$watch->strap_material}
            - Movement Type: {$watch->movement_type}
            - Water Resistance: {$watch->water_resistance}
            - Case Diameter: {$watch->case_diameter_mm}mm
            - Case Thickness: {$watch->case_thickness_mm}mm
            - Band Width: {$watch->band_width_mm}mm
            - Dial Color: {$watch->dial_color}
            - Crystal Material: {$watch->crystal_material}
            - Complications: {$watch->complications}
            - Power Reserve: {$watch->power_reserve}
            - Price: \${$watch->price_usd}
        MARKDOWN;
    }
}
