<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Display search results
     *
     * @param Request $request
     * @return View
     */
    public function search(Request $request): View
    {
        $query = $request->input('q', '');
        
        $results = $this->searchService->search($query);

        return view('search.results', [
            'results' => $results,
            'query' => $query,
        ]);
    }
}
