<?php

namespace App\Services\Assistant;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class AssistantCatalogPresenter
{
    /**
     * @param  Collection<int, Vehicle>  $vehicles
     * @return list<array<string, mixed>>
     */
    public function vehicleCards(Collection $vehicles): array
    {
        $cards = [];
        foreach ($vehicles as $vehicle) {
            $cards[] = [
                'type' => 'vehicle',
                'uuid' => (string) $vehicle->uuid,
                'title' => $this->vehicleTitle($vehicle),
                'subtitle' => $this->vehicleSubtitle($vehicle),
                'price_label' => $this->vehiclePriceLabel($vehicle),
                'image_url' => $this->vehicleImageUrl($vehicle),
                'url' => '/compra-tu-auto/detail/'.$vehicle->uuid,
            ];
        }

        return $cards;
    }

    /**
     * @param  Collection<int, BoutiqueProduct>  $products
     * @return list<array<string, mixed>>
     */
    public function boutiqueProductCards(Collection $products): array
    {
        $cards = [];
        foreach ($products as $product) {
            $cards[] = [
                'type' => 'boutique_product',
                'uuid' => (string) $product->uuid,
                'title' => (string) $product->name,
                'subtitle' => $product->category?->name,
                'price_label' => $product->price
                    ? '$'.number_format((float) $product->price, 0, '.', ',')
                    : null,
                'image_url' => $this->boutiqueImageUrl($product),
                'url' => '/boutique/product/'.$product->uuid,
            ];
        }

        return $cards;
    }

    private function vehicleTitle(Vehicle $vehicle): string
    {
        if (trim((string) $vehicle->name) !== '') {
            return trim((string) $vehicle->name);
        }

        $parts = array_filter([
            $vehicle->brand?->name,
            $vehicle->line?->name,
            $vehicle->model?->name,
        ]);

        return $parts !== [] ? implode(' ', $parts) : 'Vehículo disponible';
    }

    private function vehicleSubtitle(Vehicle $vehicle): ?string
    {
        $bits = [];
        $year = $vehicle->model?->year;
        if ($year) {
            $bits[] = 'Año '.$year;
        }
        if ($vehicle->mileage) {
            $bits[] = number_format((int) $vehicle->mileage, 0, '.', ',').' km';
        }
        if ($vehicle->dealership?->name) {
            $bits[] = $vehicle->dealership->name;
        }

        return $bits !== [] ? implode(' · ', $bits) : null;
    }

    private function vehiclePriceLabel(Vehicle $vehicle): ?string
    {
        $price = $vehicle->offer_price ?: $vehicle->sale_price ?: $vehicle->list_price;
        if (! $price) {
            return null;
        }

        return '$'.number_format((float) $price, 0, '.', ',');
    }

    private function vehicleImageUrl(Vehicle $vehicle): ?string
    {
        $vehicle->loadMissing(['firstImage', 'images']);
        $url = $vehicle->firstImage?->service_image_url;
        if ($url) {
            return (string) $url;
        }

        $first = $vehicle->images->first();

        return $first?->service_image_url ? (string) $first->service_image_url : null;
    }

    private function boutiqueImageUrl(BoutiqueProduct $product): ?string
    {
        $product->loadMissing(['images']);
        $image = $product->images
            ->where('status', 'uploaded')
            ->sortBy('sort_id')
            ->first();

        if (! $image || empty($image->image_path)) {
            return null;
        }

        return (string) $image->image_path;
    }
}
