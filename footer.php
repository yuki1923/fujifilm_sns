<footer class="footer" id="footer">
  Copyright Fujiの病 .All Rights Reserved.
</footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="./footerFixed.js"></script>
<script>
  $dropArea = $('.areaDrop');
  $inputFile = $('.inputFile');

  $dropArea.on('dragover', function(e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '3px #ccc dashed');
  });
  $dropArea.on('dragleave', function(e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'none');
  });
  $inputFile.on('change', function(e) {
    $dropArea.css('border', 'none');
    var file = this.files[0];
    $img = $(this).siblings('.prevImg');
    fileReader = new FileReader();

    fileReader.onload = function(event) {
      $img.attr('src', event.target.result).show();
    };

    fileReader.readAsDataURL(file);
  });



  // コメントエリアのテキストカウント
  $('.js-count-text').on('keyup', function(e) {
    $('.js-count-view').html($(this).val().length);
  })


  //いいね登録・削除
  var $fav,
    favPhotoId;

  $fav = $('.js-click-heart') || null;
  console.log('ファヴ' + $fav);
  favPhotoId = $fav.data('photoid') || null;
  console.log('ファヴフォト' + favPhotoId);
  if (favPhotoId !== undefined && favPhotoId !== null) {
    $fav.on('click', function() {
      var $this = $(this);
      $.ajax({
        type: "POST",
        url: "ajaxFav.php",
        data: {
          photoid: favPhotoId
        }
      }).done(function(data) {
        console.log('Ajax Success');
        //クラス属性をtoggleでつけ外しする
        $this.toggleClass('active');
      }).fail(function(msg) {
        console.log('Ajax Error');
      });
    });
  }
</script>
</body>

</html>
