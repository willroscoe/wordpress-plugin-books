jQuery(function($) {
 
    setupdeleteclicks('epub');
    setupdeleteclicks('PDF');
    setupdeleteclicks('mobi');

    function setupdeleteclicks(filetype) {
        // Check to see if the 'Delete File' link exists on the page...
        if($('a#' + filetype + '_file_attachment_delete').length === 1) {
    
            // Since the link exists, we need to handle the case when the user clicks on it...
            $('#' + filetype + '_file_attachment_delete').click(function(evt) {
            
                // We don't want the link to remove us from the current page
                // so we're going to stop it's normal behavior.
                evt.preventDefault();
                
                // Find the text input element that stores the path to the file
                // and clear it's value.
                $('#' + filetype + '_file_attachment_url').val('');
                
                // Hide this link so users can't click on it multiple times
                $(this).hide();
            
            });
        
        } // end if
    }
 
});