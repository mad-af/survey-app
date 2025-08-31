<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class WilayahController extends Controller
{
    private const BASE_URL = 'https://wilayah.id/api';

    /**
     * Get all provinces
     */
    public function getProvinces(): JsonResponse
    {
        try {
            $response = Http::timeout(30)->get(self::BASE_URL . '/provinces.json');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'error' => 'Failed to fetch provinces',
                'message' => 'Unable to retrieve province data'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => 'Unable to connect to wilayah service'
            ], 500);
        }
    }

    /**
     * Get regencies by province code
     */
    public function getRegencies(string $provinceCode): JsonResponse
    {
        try {
            $response = Http::timeout(30)->get(self::BASE_URL . '/regencies/' . $provinceCode . '.json');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'error' => 'Failed to fetch regencies',
                'message' => 'Unable to retrieve regency data for province: ' . $provinceCode
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => 'Unable to connect to wilayah service'
            ], 500);
        }
    }

    /**
     * Get districts by regency code
     */
    public function getDistricts(string $regencyCode): JsonResponse
    {
        try {
            $response = Http::timeout(30)->get(self::BASE_URL . '/districts/' . $regencyCode . '.json');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'error' => 'Failed to fetch districts',
                'message' => 'Unable to retrieve district data for regency: ' . $regencyCode
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => 'Unable to connect to wilayah service'
            ], 500);
        }
    }

    /**
     * Get villages by district code
     */
    public function getVillages(string $districtCode): JsonResponse
    {
        try {
            $response = Http::timeout(30)->get(self::BASE_URL . '/villages/' . $districtCode . '.json');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'error' => 'Failed to fetch villages',
                'message' => 'Unable to retrieve village data for district: ' . $districtCode
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => 'Unable to connect to wilayah service'
            ], 500);
        }
    }
}