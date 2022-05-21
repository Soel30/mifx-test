<?php

namespace App\Http\Controllers;

use App\Book;
use App\BookReview;
use App\Http\Requests\PostBookReviewRequest;
use App\Http\Resources\BookReviewResource;
use Illuminate\Http\Request;

class BooksReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth.admin', 'auth:api']);
    }

    public function store(int $bookId, PostBookReviewRequest $request)
    {
        // check if book exists
        $book = Book::find($bookId);
        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $bookReview = BookReview::create([
            'book_id' => $bookId,
            'review' => $request->review,
            'comment' => $request->comment,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Book review created successfully',
            'data' => new BookReviewResource($bookReview),
        ], 200);
    }

    public function destroy(int $bookId, int $reviewId, Request $request)
    {
        $book = Book::find($bookId);
        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book not found',
            ], 404);
        }

        $review = BookReview::find($reviewId);
        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Book review not found',
            ], 404);
        }

        $review->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Book review deleted successfully',
        ], 200);
    }
}
