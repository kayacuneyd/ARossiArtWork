<?php
/**
 * Image Processing Class
 * Handles upload, resize, thumbnail, WebP conversion, EXIF stripping
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

class ImageProcessor {
    private $maxWidth;
    private $thumbWidth;
    private $uploadDir;
    private $thumbDir;
    private $webpDir;
    private $useImagick;
    private $gdWebpSupported;
    
    public function __construct() {
        $this->maxWidth = MAX_IMAGE_WIDTH;
        $this->thumbWidth = THUMB_WIDTH;
        $this->uploadDir = UPLOAD_DIR;
        $this->thumbDir = THUMB_DIR;
        $this->webpDir = WEBP_DIR;
        $this->useImagick = extension_loaded('imagick');
        $this->gdWebpSupported = function_exists('imagewebp');
        
        // Ensure directories exist
        $this->ensureDirectories();
    }
    
    /**
     * Ensure upload directories exist
     */
    private function ensureDirectories() {
        $dirs = [$this->uploadDir, $this->thumbDir, $this->webpDir];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Process uploaded image
     * Returns array with filenames or false on error
     */
    public function processUpload($file, $customFilename = null) {
        // Validate file
        $errors = validate_image_file($file);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        // Generate filename
        $filename = $customFilename ?: generate_unique_filename($file['name']);
        
        // Process based on available extension
        if ($this->useImagick) {
            return $this->processWithImagick($file['tmp_name'], $filename);
        } else {
            return $this->processWithGD($file['tmp_name'], $filename);
        }
    }
    
    /**
     * Process image with Imagick
     */
    private function processWithImagick($sourcePath, $filename) {
        try {
            $image = new Imagick($sourcePath);
            
            // Strip EXIF and metadata
            $image->stripImage();
            
            // Auto-orient based on EXIF (before stripping)
            $image->autoOrientImage();
            
            // Get dimensions
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();
            
            // Set quality
            $image->setImageCompressionQuality(85);
            
            // Resize main image if needed
            if ($width > $this->maxWidth) {
                $newHeight = intval(($this->maxWidth / $width) * $height);
                $image->resizeImage($this->maxWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);
            }
            
            // Save main image
            $mainPath = $this->uploadDir . $filename;
            $image->writeImage($mainPath);
            
            // Create thumbnail
            $thumb = clone $image;
            if ($thumb->getImageWidth() > $this->thumbWidth) {
                $thumbHeight = intval(($this->thumbWidth / $thumb->getImageWidth()) * $thumb->getImageHeight());
                $thumb->resizeImage($this->thumbWidth, $thumbHeight, Imagick::FILTER_LANCZOS, 1);
            }
            $thumbPath = $this->thumbDir . $filename;
            $thumb->writeImage($thumbPath);
            $thumb->destroy();
            
            // Create WebP version (skip if unsupported)
            $webpFilename = null;
            try {
                $formats = method_exists('Imagick', 'queryFormats') ? Imagick::queryFormats('WEBP') : [];
                if (!empty($formats)) {
                    $webp = clone $image;
                    $webpFilename = $this->changeExtension($filename, 'webp');
                    $webpPath = $this->webpDir . $webpFilename;
                    $webp->setImageFormat('webp');
                    $webp->setImageCompressionQuality(80);
                    $webp->writeImage($webpPath);
                    $webp->destroy();
                }
            } catch (Exception $webpException) {
                $webpFilename = null;
                error_log('[ImageProcessor] Imagick WebP conversion skipped: ' . $webpException->getMessage());
            }
            
            // Clean up
            $image->destroy();
            
            return [
                'filename' => $filename,
                'thumbnail' => $filename,
                'webp' => $webpFilename,
                'dimensions' => $this->getImageDimensions($mainPath)
            ];
            
        } catch (Exception $e) {
            throw new Exception("Imagick processing failed: " . $e->getMessage());
        }
    }
    
    /**
     * Process image with GD (fallback)
     */
    private function processWithGD($sourcePath, $filename) {
        try {
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                throw new Exception("Invalid image file");
            }
            
            list($width, $height, $type) = $imageInfo;
            
            // Create image resource based on type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $source = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    throw new Exception("Unsupported image type");
            }
            
            if (!$source) {
                throw new Exception("Failed to create image resource");
            }
            
            // Calculate new dimensions for main image
            if ($width > $this->maxWidth) {
                $newWidth = $this->maxWidth;
                $newHeight = intval(($this->maxWidth / $width) * $height);
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            
            // Create and save main image
            $mainImage = imagecreatetruecolor($newWidth, $newHeight);
            $this->preserveTransparency($mainImage, $type);
            imagecopyresampled($mainImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            $mainPath = $this->uploadDir . $filename;
            $this->saveImage($mainImage, $mainPath, $type);
            
            // Create thumbnail
            $thumbWidth = min($this->thumbWidth, $newWidth);
            $thumbHeight = intval(($thumbWidth / $newWidth) * $newHeight);
            
            $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
            $this->preserveTransparency($thumbImage, $type);
            imagecopyresampled($thumbImage, $mainImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $newWidth, $newHeight);
            
            $thumbPath = $this->thumbDir . $filename;
            $this->saveImage($thumbImage, $thumbPath, $type);
            
            // Create WebP version if supported
            $webpFilename = null;
            if ($this->gdWebpSupported) {
                $webpFilename = $this->changeExtension($filename, 'webp');
                $webpPath = $this->webpDir . $webpFilename;
                if (!imagewebp($mainImage, $webpPath, 80)) {
                    $webpFilename = null;
                    error_log('[ImageProcessor] GD WebP conversion failed for ' . $filename);
                }
            }
            
            // Clean up
            imagedestroy($source);
            imagedestroy($mainImage);
            imagedestroy($thumbImage);
            
            return [
                'filename' => $filename,
                'thumbnail' => $filename,
                'webp' => $webpFilename,
                'dimensions' => $newWidth . 'x' . $newHeight
            ];
            
        } catch (Exception $e) {
            throw new Exception("GD processing failed: " . $e->getMessage());
        }
    }
    
    /**
     * Preserve transparency for PNG/WebP
     */
    private function preserveTransparency($image, $type) {
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
        }
    }
    
    /**
     * Save image with GD
     */
    private function saveImage($image, $path, $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image, $path, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $path, 8);
                break;
            case IMAGETYPE_WEBP:
                if (!$this->gdWebpSupported) {
                    throw new Exception('WebP uploads are not supported on this server. Please upload JPG or PNG instead.');
                }
                imagewebp($image, $path, 85);
                break;
        }
    }
    
    /**
     * Change file extension
     */
    private function changeExtension($filename, $newExt) {
        return pathinfo($filename, PATHINFO_FILENAME) . '.' . $newExt;
    }
    
    /**
     * Get image dimensions as string
     */
    private function getImageDimensions($path) {
        $info = getimagesize($path);
        return $info ? $info[0] . 'x' . $info[1] : '';
    }
    
    /**
     * Delete artwork files
     */
    public function deleteArtwork($filename, $webpFilename = null) {
        $deleted = true;
        
        // Delete main image
        $mainPath = $this->uploadDir . $filename;
        if (file_exists($mainPath)) {
            $deleted = $deleted && unlink($mainPath);
        }
        
        // Delete thumbnail
        $thumbPath = $this->thumbDir . $filename;
        if (file_exists($thumbPath)) {
            $deleted = $deleted && unlink($thumbPath);
        }
        
        // Delete WebP
        if ($webpFilename) {
            $webpPath = $this->webpDir . $webpFilename;
            if (file_exists($webpPath)) {
                $deleted = $deleted && unlink($webpPath);
            }
        }
        
        return $deleted;
    }
    
    /**
     * Check if Imagick is available
     */
    public function isImagickAvailable() {
        return $this->useImagick;
    }
}
