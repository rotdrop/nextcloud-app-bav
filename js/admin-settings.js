$(function(){
  $('#bav-modal').on('change', function(event) {
    const value = $(this).prop('checked');

    $.post(
      OC.generateUrl('/apps/bav/settings/admin/set/modal'),
      { 'value': value })
    .done(function(data) {
       console.info(data);
       $('#bav-admin-settings .msg').html(data.message);
       $('#bav-admin-settings .msg').show();
     })
    .fail(function(jqXHR) {
       const response = JSON.parse(jqXHR.responseText);
       console.log(response);
          if (response.message) {
	    $('#bav-admin-settings .msg').html(response.message);
            $('#bav-admin-settings .msg').show();
          }
     });
      
  });
});