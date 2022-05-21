<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Requests\PostBookRequest;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;

class BooksController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'auth.admin'])->except('index');
    }

    public function index(Request $request)
    {
        $title = $request->title;
        $per_page = $request->per_page ?: 10;
        $sortDirection = strtolower($request->sort_direction) == 'asc' ? 'asc' : 'desc';
        $sortColumn = $request->sortColumn;
        $authors = $request->authors;
        $avg = $request->avg;

        $q_books = Book::query();

        $q_books->when($title, function ($q_books, $title) {
            return $q_books->where('title', 'like', "%$title%");
        });

        $q_books->when($authors, function ($q_books, $authors) {
            return $q_books->whereHas('authors', function ($q_authors) use ($authors) {
                $q_authors->whereIn('id', [$authors]);
            });
        });

        $q_books->when($avg, function ($q_books, $avg) {
            return $q_books->whereHas('reviews', function ($q_reviews) use ($avg) {
                $q_reviews->selectRaw('book_id, avg(review) as avg');
                $q_reviews->groupBy('book_id');
                $q_reviews->havingRaw('avg >= ?', [$avg]);
            });
        });

        $q_books->when($sortColumn, function ($q_books, $sortColumn) use ($sortDirection) {
            if ($sortColumn == 'avg_review') {
                $q_books->leftJoin('book_reviews', 'books.id', '=', 'book_reviews.book_id')
                    ->selectRaw('books.*, avg(review) as avg')
                    ->groupBy('books.id')
                    ->orderBy('avg', $sortDirection);
            } else {
                return $q_books->orderBy($sortColumn, $sortDirection);
            }
        });

        $books = $q_books->with('authors', 'reviews')->latest('id')->paginate($per_page);

        return response()->json([
            'data' => BookResource::collection($books),
            'links' => [
                'first' => $books->url(1),
                'last' => $books->url($books->lastPage()),
                'prev' => $books->previousPageUrl(),
                'next' => $books->nextPageUrl(),
            ],
            'meta' => [
                'total' => $books->total(),
                'per_page' => $books->perPage(),
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'from' => $books->firstItem(),
                'to' => $books->lastItem(),
                'path' => $books->path(),
            ],
        ]);
    }

    public function store(PostBookRequest $request)
    {
        $book = Book::create([
            'isbn' => $request->isbn,
            'title' => $request->title,
            'published_year' => $request->published_year,
            'description' => $request->description,
        ]);

        $book->authors()->sync($request->authors);

        return $this->apiResponseSuccess(new BookResource($book), 'Book created successfully', 204);
    }
}
