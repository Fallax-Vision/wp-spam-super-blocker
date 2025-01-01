jQuery(document).ready(function($) {
  // Handle the "Load New Blocked Words" button click
  $('#ssb_load_new_keywords').on('click', function(e) {
    e.preventDefault();
    $('#ssb_progress_bar').show();
    let totalNewKeywords = 0;
    ssb_fetch_and_update_keywords_incrementally(0, null, totalNewKeywords);
  });

  function ssb_fetch_and_update_keywords_incrementally(offset, total_count, totalNewKeywords) {
    $.ajax({
      url: ssb_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'ssb_fetch_and_update_keywords_incrementally',
        offset: offset,
        total_count: total_count,
        ssb_nonce: ssb_ajax.ssb_nonce
      },
      success: function(response) {
        if (response.success) {
          var progress_bar = $('#ssb_progress_bar_inner');
          var progress = (offset / total_count) * 100;
          progress_bar.css('width', progress + '%');
          progress_bar.text(Math.round(progress) + '%');

          if (response.data.message === 'added_0') {
            // Stop the loop if no new keywords are added
            $('#ssb_progress_bar').hide();
            alert('No new keywords to add.');
            return;
          } else if (response.data.message.startsWith('keywords_added_')) {
            // Accumulate the number of new keywords added
            totalNewKeywords += parseInt(response.data.message.split('_')[2]);
          }

          // Continue fetching the next batch if there are more keywords
          if (response.data.offset < response.data.total_count) {
            ssb_fetch_and_update_keywords_incrementally(response.data.offset, response.data.total_count, totalNewKeywords);
          } else {
            // Show the alert with the total number of new keywords added
            if (totalNewKeywords > 0) {
              alert('Added ' + totalNewKeywords + ' new keywords.');
            }
            // Delete the cookies
            document.cookie = "all_remote_keys=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "new_remote_keys=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            // Reload the page to update the stats
            location.reload();
          }
        } else {
          alert('Error: ' + response.data.message);
          $('#ssb_progress_bar').hide();
        }
      },
      error: function(xhr, status, error) {
        console.error('An error occurred while fetching keywords:', error);
        console.error('XHR Status:', status);
        console.error('XHR Response:', xhr.responseText);
        alert('An error occurred while fetching keywords.');
        $('#ssb_progress_bar').hide();
      }
    });
  }
});
