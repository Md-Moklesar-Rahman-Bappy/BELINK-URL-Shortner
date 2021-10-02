<?php

namespace App\Http\Controllers;

use App\Link;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Common\Core\BaseController;

class QrCodeController extends BaseController
{
    /**
     * @var Link
     */
    private $link;

    /**
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    public function show($linkHash)
    {
        $link = $this->link
            ->where('hash', $linkHash)
            ->orWhere('alias', $linkHash)
            ->firstOrFail();

        $this->authorize('show', $link);


        $renderer = new ImageRenderer(
            new RendererStyle(160),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $response = $writer->writeString("{$link->short_url}?source=qr");

        return response()->stream(function() use($response) {
            echo $response;
        }, 200, ['Content-Type' => 'image/svg+xml', 'Content-Length: ' . strlen($response)]);
    }
}
