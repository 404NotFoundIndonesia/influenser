<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait HasPicture
{
    protected string $nameColumn = 'name';
    protected string $picturePathColumn = 'picture_path';

    public function pictureUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->{$this->picturePathColumn}) {
                return Storage::url($this->{$this->picturePathColumn});
            }

            return $this->defaultPictureUrl();
        });
    }

    public function deletePicture(): void
    {
        if ($this->{$this->picturePathColumn}) {
            Storage::delete($this->{$this->picturePathColumn});
        }
    }

    private function defaultPictureUrl(): string
    {
        $name = trim(collect(explode(' ', $this->{$this->nameColumn}))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }
}
