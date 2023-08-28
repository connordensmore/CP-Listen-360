<?php
// reviews-fetcher.php

// Function to fetch and update reviews
function listen360_fetch_reviews()
{
  // Implement your logic to fetch reviews from the Listen360 API here
  // You can use wp_remote_get() to make API requests
  // Update or insert reviews into the custom post type
}

// Schedule a daily event to fetch reviews
function listen360_schedule_reviews_fetch()
{
  if (!wp_next_scheduled('listen360_fetch_reviews_event')) {
    wp_schedule_event(time(), 'daily', 'listen360_fetch_reviews_event');
  }
}
add_action('wp', 'listen360_schedule_reviews_fetch');

// Hook to fetch reviews when the scheduled event triggers
function listen360_fetch_reviews_event_handler()
{
  listen360_fetch_reviews();
}
add_action('listen360_fetch_reviews_event', 'listen360_fetch_reviews_event_handler');