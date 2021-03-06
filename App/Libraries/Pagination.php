<?php

namespace App\Libraries;

use Exception;

/**
 * To show pagination bar.
 *
 * @category Library
 * @package  Blog
 */
class Pagination
{
    // Page number on which we are on pagination bar
    protected $page;

    // Total number of elements (articles, comments etc.) in database
    protected $total;

    // Elements per page. (how many we showing)
    protected $limit;

    // Number of calculated pages (total/limit rounded up) on pagination bar
    protected $pages;

    // Calculated items (previous + pages + next)
    protected $items = [];

    // First page on pagination bar = 1
    protected $first = 1;

    // Number of last page on pagination bar
    protected $last;

    // Number of previous page on pagination bar
    protected $previous;

    // Number of next page on pagination bar
    protected $next;

    /**
     * Set page number on which we are on pagination bar
     *
     * @param int $page number on
     *
     * @return void
     */
    public function setPage(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Set total number of elements
     *
     * @param int $total total number
     *
     * @return void
     */
    public function setTotal(int $total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Set elements per page.
     *
     * @param int $limit limit how many we showing
     *
     * @return void
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get calculated items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get number of calculated pages
     *
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Show array with all data (using for pagination bar)
     *
     * @return array
     */
    public function calculate()
    {
        if (!isset($this->limit) || !isset($this->total) || !isset($this->page)) {
            throw new Exception('Pagination data not set');
        }

        // Set number of pages. ceil- Returns the next highest integer value by rounding up value
        $this->pages = (int) ceil($this->total / $this->limit);

        // Set rules for 'next'
        if ($this->page < $this->pages) {
            $this->next = $this->page + 1;
        } else {
            $this->next = null;
        }

        // Set rules for 'previous'
        if ($this->page > $this->first) {
            $this->previous = $this->page - 1;
        } else {
            $this->previous = null;
        }

        // Set number for last page.
        $this->last = $this->pages;

        $items = [];

        // Set label + number for previous
        $items[] = [
            'label' => 'previous',
            'number' => $this->previous,
        ];
        // Number of pages in pagination bar = range — Create an array containing a range of elements
        $pages = range($this->first, $this->last);


        foreach ($pages as $page) {
            $items[] = [
                // If $item == $this->page true = set label to current if false set to number.
                'label' => $page == $this->page ? 'current' : $page,
                'number' => $page,
            ];
        }

        // Set label + number for next
        $items[] = [
            'label' => 'next',
            'number' => $this->next,
        ];

        // Return array where each element has: label and number (elements on pagination bar)
        $this->items = $items;

        return $this->items;
    }
}
