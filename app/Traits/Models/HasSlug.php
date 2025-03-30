<?php

namespace App\Traits\Models;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Generate a unique slug based on the name attribute.
     */
    public function generateSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $count = 1;

        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Check if the slug already exists in the database.
     */
    protected function slugExists(string $slug): bool
    {
        return static::where('slug', $slug)->exists();
    }
}
