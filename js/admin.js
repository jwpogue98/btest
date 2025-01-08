jQuery(document).ready(function($) {
    var frame;
    
    $('#add_bicycle_images').on('click', function(e) {
        e.preventDefault();
        
        if (frame) {
            frame.open();
            return;
        }
        
        frame = wp.media({
            title: 'Select Bicycle Images',
            button: {
                text: 'Add to gallery'
            },
            multiple: true
        });
        
        frame.on('select', function() {
            var attachments = frame.state().get('selection').map(function(attachment) {
                attachment = attachment.toJSON();
                return {
                    id: attachment.id,
                    url: attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url
                };
            });
            
            var ids = $('#bicycle_gallery').val();
            ids = ids ? ids.split(',') : [];
            
            attachments.forEach(function(attachment) {
                if (!ids.includes(attachment.id.toString())) {
                    ids.push(attachment.id);
                    $('#bicycle-gallery-preview').append(
                        '<div class="gallery-image" data-id="' + attachment.id + '">' +
                        '<img src="' + attachment.url + '" />' +
                        '<button class="remove-image">×</button>' +
                        '</div>'
                    );
                }
            });
            
            $('#bicycle_gallery').val(ids.join(','));
        });
        
        frame.open();
    });
    
    $('#bicycle-gallery-preview').on('click', '.remove-image', function(e) {
        e.preventDefault();
        var container = $(this).parent();
        var id = container.data('id').toString();
        var ids = $('#bicycle_gallery').val().split(',');
        ids = ids.filter(function(val) { return val !== id; });
        $('#bicycle_gallery').val(ids.join(','));
        container.remove();
    });
});