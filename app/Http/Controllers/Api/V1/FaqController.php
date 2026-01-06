<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends BaseController
{
    /**
     * List all active FAQs (public endpoint).
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->get('category');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $query = Faq::active();

        // Filter by category
        if ($category) {
            $query->category($category);
        }

        // Search in question and answer
        if ($search) {
            $query->search($search);
        }

        $faqs = $query->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($faqs, 'FAQs retrieved successfully');
    }

    /**
     * Get single FAQ (public endpoint, increments views).
     */
    public function show(int $id): JsonResponse
    {
        $faq = Faq::active()->find($id);

        if (!$faq) {
            return $this->notFoundResponse('FAQ not found');
        }

        // Increment view count
        $faq->incrementViews();

        return $this->successResponse($faq, 'FAQ retrieved successfully');
    }
}

