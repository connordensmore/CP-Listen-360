<?php

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
  $api_url = 'https://app.listen360.com/organizations/2835632886631181125/reviews.csv?per_page=100';
  $api_key = 'cbbef8d18aca2221e423d81752a420a3197bf411';
  $authorization_header = 'Authorization: Basic ' . base64_encode($api_key . ':X');
  $cookie_header = 'Cookie: _listen360_production_session=BAh7CUkiD3Nlc3Npb25faWQGOgZFVEkiJTQ0MTA2YmMxNjUzMWYzOGRjNDY1NTdmODE4N2M0MWU2BjsAVEkiDnJldHVybl90bwY7AEZJIh9odHRwczovL2FwcC5saXN0ZW4zNjAuY29tLwY7AFRJIhBfY3NyZl90b2tlbgY7AEZJIjFxQVNLd0F4ZWZQd0JQcDYxcWNvazVLc0xyL2lGNlM4eC9WckFkcUJOT3EwPQY7AEZJIgxyb290X2lkBjsARmwrCUUrESkWMVon--a9488c80e4d5662c554e4a898d374aaf9277ecbd';

  $headers = array(
    $authorization_header,
    $cookie_header,
  );

  $api_response = fetch_api_data($api_url, $headers);

  $csv_rows = str_getcsv($api_response, "\n");

  foreach ($csv_rows as $csv_row) {
    $data = str_getcsv($csv_row);

    // Make sure the array has at least 17 elements before accessing them
    if (count($data) >= 17) {
      $customerFullName = $data[3];
      $publicDisplayComments = $data[19];

      // Check if the cells are not empty before creating or updating the post
      if (!empty($customerFullName) && !empty($publicDisplayComments)) {
        $existing_post = get_page_by_title($customerFullName, OBJECT, 'listen360_review');

        if ($existing_post) {
          $post_id = $existing_post->ID;

          // Update the existing post
          wp_update_post(
            array(
              'ID' => $post_id,
              'post_content' => $publicDisplayComments,
            )
          );

          // Update custom fields
          update_post_meta($post_id, 'organization_reference', $data[0]);
          update_post_meta($post_id, 'organization_name', $data[1]);
          update_post_meta($post_id, 'customer_reference', $data[2]);
          update_post_meta($post_id, 'customer_email', $data[4]);
          update_post_meta($post_id, 'job_reference', $data[7]);
          update_post_meta($post_id, 'unique_survey_id', $data[8]);
          update_post_meta($post_id, 'loyalty_profile_label', $data[9]);
          update_post_meta($post_id, 'rating', $data[10]);
          update_post_meta($post_id, 'survey_sent', $data[11]);
          update_post_meta($post_id, 'survey_completed', $data[12]);
          update_post_meta($post_id, 'comments', $data[16]);
          update_post_meta($post_id, 'last_updated', $data[18]);
          update_post_meta($post_id, 'public_display_customer_name', $data[20]);

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

          // Set custom fields for the new post
          update_post_meta($post_id, 'organization_reference', $data[0]);
          update_post_meta($post_id, 'organization_name', $data[1]);
          update_post_meta($post_id, 'customer_reference', $data[2]);
          update_post_meta($post_id, 'customer_email', $data[4]);
          update_post_meta($post_id, 'job_reference', $data[7]);
          update_post_meta($post_id, 'unique_survey_id', $data[8]);
          update_post_meta($post_id, 'loyalty_profile_label', $data[9]);
          update_post_meta($post_id, 'rating', $data[10]);
          update_post_meta($post_id, 'survey_sent', $data[11]);
          update_post_meta($post_id, 'survey_completed', $data[12]);
          update_post_meta($post_id, 'comments', $data[16]);
          update_post_meta($post_id, 'last_updated', $data[18]);
          update_post_meta($post_id, 'public_display_customer_name', $data[20]);
        }
      }
    }
  }
}


// Hook the function to run when the page /testimonials/ is visited
function run_fetch_process_store_on_page_visit()
{
  if (is_page('testimonials')) {
    fetch_process_store_reviews();
  }
}
add_action('wp', 'run_fetch_process_store_on_page_visit');