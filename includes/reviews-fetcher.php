<?php
// reviews-fetcher.php

// Function to fetch and update reviews
function listen360_fetch_reviews()
{
  $api_key = get_option('listen360_api_key');
  $org_id = "2835632886631181125";
  $organization_reference = get_option('listen360_organization_reference');

  $authorization_header = 'Basic ' . base64_encode($api_key . ':X');
  $headers = array(
    'Authorization' => $authorization_header,
  );

  $url = 'https://app.listen360.com/organizations/' . $org_id . '/reviews.xml?per_page=20000';
  $response = wp_remote_get($url, array('headers' => $headers));

  if (is_wp_error($response)) {
    // Handle error
    return;
  }

  $body = wp_remote_retrieve_body($response);
  $xml = simplexml_load_string($body);

  // Loop through XML response and filter reviews by organization-reference
  $filtered_reviews = array();

  foreach ($xml->survey as $review) {
    $review_organization_reference = (int) $review->{'organization-reference'};

    if ($review_organization_reference === (int) $organization_reference) {
      $filtered_reviews[] = $review;
    }
  }

  // Loop through filtered reviews and update custom post type
  foreach ($filtered_reviews as $review) {
    $customer_name = (string) $review->{'customer-full-name'};
    $rating = (int) $review->{'recommendation-likelihood'};
    $comments = (string) $review->{'public-display-comments'};

    $post_data = array(
      'post_title' => 'Review by ' . $customer_name,
      'post_content' => $comments,
      'post_status' => 'publish',
      'post_type' => 'listen360_review', // Adjust based on your custom post type
    );

    $post_id = wp_insert_post($post_data);

    update_post_meta($post_id, 'rating', $rating);
    // Add more custom fields here

    // Attach post to categories or taxonomies if needed
    // wp_set_post_categories($post_id, $category_ids);
    // wp_set_object_terms($post_id, $term_ids, 'taxonomy_name');
  }
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