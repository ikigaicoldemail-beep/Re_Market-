<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addressService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'addresses' => AddressResource::collection(
                $this->addressService->list($request->user())
            ),
        ]);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Address created successfully.',
            'address' => new AddressResource($address),
        ], 201);
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $address = $this->addressService->update($address, $request->validated());

        return response()->json([
            'message' => 'Address updated successfully.',
            'address' => new AddressResource($address),
        ]);
    }

    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $this->addressService->delete($address);

        return response()->json([
            'message' => 'Address deleted successfully.',
        ]);
    }
}
