<?php

namespace Tests\Unit\Boutique;

use App\Services\Boutique\BoutiqueExternalImageUrlResolver;
use PHPUnit\Framework\TestCase;

class BoutiqueExternalImageUrlResolverTest extends TestCase
{
    private BoutiqueExternalImageUrlResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new BoutiqueExternalImageUrlResolver;
    }

    public function test_detects_drive_share_links(): void
    {
        $url = 'https://drive.google.com/file/d/1AbC-dEfGhIj/view?usp=sharing';
        $this->assertTrue($this->resolver->isGoogleDriveUrl($url));
        $this->assertSame('1AbC-dEfGhIj', $this->resolver->extractDriveFileId($url));
    }

    public function test_extracts_id_from_open_link(): void
    {
        $url = 'https://drive.google.com/open?id=xyz99_AB';
        $this->assertSame('xyz99_AB', $this->resolver->extractDriveFileId($url));
    }

    public function test_direct_download_url(): void
    {
        $this->assertSame(
            'https://drive.google.com/uc?export=download&id=abc123',
            $this->resolver->directDownloadUrl('abc123')
        );
    }

    public function test_rejects_direct_image_cdn(): void
    {
        $url = 'https://cdn.example.com/producto.jpg';
        $this->assertFalse($this->resolver->isGoogleDriveUrl($url));
        $this->assertNull($this->resolver->extractDriveFileId($url));
    }
}
