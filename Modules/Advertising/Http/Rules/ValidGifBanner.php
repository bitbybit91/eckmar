<?php

namespace Modules\Advertising\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validates that an uploaded file is a proper GIF (checks magic bytes) and
 * that its dimensions exactly match the required banner dimensions.
 */
class ValidGifBanner implements Rule
{
    /** @var string */
    private $message = 'The banner file must be a valid GIF image.';

    /**
     * Determine if the validation rule passes.
     *
     * @param string                                   $attribute
     * @param \Illuminate\Http\UploadedFile|mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$value instanceof \Illuminate\Http\UploadedFile) {
            return false;
        }

        $path = $value->getRealPath();

        // Verify GIF magic bytes (GIF87a or GIF89a).
        $fh = @fopen($path, 'rb');

        if ($fh === false) {
            $this->message = 'The banner file could not be read.';
            return false;
        }

        $magic = fread($fh, 6);
        fclose($fh);

        if ($magic !== 'GIF87a' && $magic !== 'GIF89a') {
            $this->message = 'The banner file does not appear to be a valid GIF image.';
            return false;
        }

        // Verify image dimensions.
        $info = @getimagesize($path);

        if ($info === false) {
            $this->message = 'The banner file dimensions could not be determined.';
            return false;
        }

        $requiredWidth  = (int) config('advertising.banner_width', 468);
        $requiredHeight = (int) config('advertising.banner_height', 60);

        if ((int) $info[0] !== $requiredWidth || (int) $info[1] !== $requiredHeight) {
            $this->message = "The banner image must be exactly {$requiredWidth}×{$requiredHeight} pixels.";
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}
