<?php
// reviews-fetcher.php

// Function to fetch and update reviews
function fetch_api_data($api_url, $headers)
{
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $headers,
    )
  );
  $response = curl_exec($curl);
  curl_close($curl);
  return $response;
}

function fetch_process_store_reviews()
{
  $organization_reference_id = get_option('organization_reference_id');
  $api_url_base = 'https://app.listen360.com/organizations/2835632886631181125/reviews.csv';
  $api_key = get_option('listen360_api_key');
  $authorization_header = 'Authorization: Basic ' . base64_encode($api_key . ':X');
  $cookie_header = 'Cookie: _listen360_production_session=BAh7CUkiD3Nlc3Npb25faWQGOgZFVEkiJTQ0MTA2YmMxNjUzMWYzOGRjNDY1NTdmODE4N2M0MWU2BjsAVEkiDnJldHVybl90bwY7AEZJIh9odHRwczovL2FwcC5saXN0ZW4zNjAuY29tLwY7AFRJIhBfY3NyZl90b2tlbgY7AEZJIjFxQVNLd0F4ZWZQd0JQcDYxcWNvazVLc0xyL2lGNlM4eC9WckFkcUJOT3EwPQY7AEZJIgxyb290X2lkBjsARmwrCUUrESkWMVon--a9488c80e4d5662c554e4a898d374aaf9277ecbd';

  $headers = array(
    $authorization_header,
    $cookie_header,
  );

  $page = 1; // Start with the first page
  $max_pages = 5; // Maximum number of pages to fetch

  while ($page <= $max_pages) {
    $api_url = $api_url_base . "?per_page=5000&page=$page";

    $api_response = fetch_api_data($api_url, $headers);

    $csv_rows = str_getcsv($api_response, "\n");

    if (empty($csv_rows)) {
      // No more data, exit the loop
      break;
    }

    foreach ($csv_rows as $csv_row) {
      $data = str_getcsv($csv_row);

      // Make sure the array has at least 17 elements before accessing them
      if (count($data) >= 17) {
        $organizationReference = $data[0];
        $customerFullName = $data[3];
        $publicDisplayComments = $data[19];

        // Format Date and Time for Survey Sent
        $surverySentString = $data[11];
        $surverySent_DateTime = new DateTime($surverySentString);
        $surverySentFormatted = $surverySent_DateTime->format('F j, Y');

        // Format Date and Time for Survey Completed
        $surveryCompletedString = $data[12];
        $surveryCompleted_DateTime = new DateTime($surveryCompletedString);
        $surveryCompletedFormatted = $surveryCompleted_DateTime->format('F j, Y');

        // Format Date and Time for Survey Last Updated
        $surveryLastUpdatedString = $data[18];
        $surveryLastUpdated_DateTime = new DateTime($surveryLastUpdatedString);
        $surveryLastUpdatedFormatted = $surveryLastUpdated_DateTime->format('F j, Y');

        // Determine the review type based on $publicDisplayComments
        $review_type = !empty($publicDisplayComments) ? 'text-review' : 'rating-only-review';

        // Check if the cells are not empty before creating or updating the post
        if ($organizationReference == "$organization_reference_id" && !empty($customerFullName)) {
          $existing_post = get_page_by_title($customerFullName, OBJECT, 'listen360_review');

          // Prepare the meta data for the post
          $meta_data = array(
            'organization_reference' => $data[0],
            'organization_name' => $data[1],
            'customer_reference' => $data[2],
            'customer_email' => $data[4],
            'job_reference' => $data[7],
            'unique_survey_id' => $data[8],
            'loyalty_profile_label' => $data[9],
            'rating' => $data[10],
            'survey_sent' => $surverySentFormatted,
            'survey_completed' => $surveryCompletedFormatted,
            'comments' => $data[16],
            'last_updated' => $surveryLastUpdatedFormatted,
            'public_display_customer_name' => $data[20]
          );

          if ($existing_post) {
            $post_id = $existing_post->ID;

            // Update the existing post
            wp_update_post(
              array(
                // 'post_title' => $customerFullName, Somehow this was updating the title of the page when retiriving and making or updating new posts????
                'post_content' => $publicDisplayComments,
              )
            );
          } else {
            // Create a new post
            $post_id = wp_insert_post(
              array(
                'post_title' => $customerFullName,
                'post_content' => $publicDisplayComments,
                'post_status' => 'publish',
                'post_type' => 'listen360_review'
              )
            );
          }

          // Update meta data for both new and existing posts
          foreach ($meta_data as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
          }

          // Set the review type taxonomy based on the condition
          wp_set_object_terms($post_id, $review_type, 'review_types');
        }
      }
    }

    $page++; //Move to the next page.

  }
}

// Add action for manual update
function listen360_manual_update_reviews()
{
  fetch_process_store_reviews();
  wp_die(); // This is necessary to end the AJAX request
}
add_action('wp_ajax_manual_update_reviews', 'listen360_manual_update_reviews');