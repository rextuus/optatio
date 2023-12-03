<?php

declare(strict_types=1);

namespace App\Content\Image;

use App\Entity\Image;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Exception\ApiError;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class CloudinaryApiGateway
{

    private Cloudinary $cloudinary;


    public function __construct(
        private ParameterBagInterface $parameterBag,
        string $cloudname,
        string $apiKey,
        string $apiSecret
    ) {
        $config = new Configuration();
        $config->cloud->cloudName = $cloudname;
        $config->cloud->apiKey = $apiKey;
        $config->cloud->apiSecret = $apiSecret;
        $config->url->secure = true;
        $this->cloudinary = new Cloudinary($config);
    }

    public function test()
    {
        $this->cloudinary->uploadApi()->upload(
            'https://upload.wikimedia.org/wikipedia/commons/a/ae/Olympic_flag.jpg',
            ['public_id' => 'olympic_flag']
        );
    }

    public function uploadImage(Image $image): ?ApiResponse
    {
        $path = $this->parameterBag->get('kernel.project_dir') . '/public/' . $image->getFilePath();
        $public_id = 'optatio/uploads/' . $image->getOwner()->getFullName(). '/'.$image->getName();

        $response = null;
        try {
            $response = $this->cloudinary->uploadApi()->upload(
                $path,
                [
                    'public_id' => $public_id,
                ]
            );
        } catch (ApiError $e) {
            $response = null;
        }

        return $response;
    }
}