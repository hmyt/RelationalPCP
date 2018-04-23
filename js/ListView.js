/* globals $, URI */
// ///////////////////////
// /
// /ListView Class
// /
// ///////////////////////
function ListView (tag, imgpath) {
  this.tag = tag
  this.imgpath = imgpath
}
/**
 * [単純に一覧を表示するメソッド]
 * @param  [array] data [屏風リスト配列]
 * @return [void]
 */
ListView.prototype.display = function (data) {
  $('#' + this.tag).append(
    $('<ul/>').attr('id', 'content')
  )
  var caller = this
  data.items.forEach(function (val, i) {
    caller._writeImage('content', '#' + val.屏風番号, {'src': caller.imgpath + val.画像ファイル名 + '/thumbnail/thumbnail_' + val.画像ファイル名 + '.png', 'height': '128', 'width': '128'}, val.屏風番号)
  })
}

/**
 * [画像書出しメソッド]
 * @param  [string,string,object] tag,href,img [対象のタグ, ハイパーリンク, 画像タグのオブジェクト, タイトル]
 * @return [void]
 */
ListView.prototype._writeImage = function (tag, href, img, title) {
  $('#' + tag).append(
    $('<li/>').attr('class', 'context').append(
      $('<p class="byobu_no">' + title + '</p>')
    ).append(
      $('<a/>').attr({'href': href, 'class': 'byobu_thumb'}).append(
        $('<img/>').attr(img)
      )
    )
  )
}

// ///////////////////////
// /
// /SortedListView Class (extends ListView)
// /
// ///////////////////////

var inherits = function (childCtor, parentCtor) {
  Object.setPrototypeOf(childCtor.prototype, parentCtor.prototype)
}

function SortedListView (tag, imgpath) {
  this.tag = tag
  this.imgpath = imgpath
}

// 継承
inherits(SortedListView, ListView)

/**
 * [sort済みの一覧を表示するメソッド]
 * @param  [array] data [屏風リスト配列]
 * @return [void]
 */
SortedListView.prototype.display = function (data) {
  var title
  if (data['param']['param'] === 'list_kinsei_kimono') {
    title = '近世きもの万華鏡'
  } else if (data['param']['param'] === 'list_kimono_monyo') {
    title = '文様文献表示'
  } else if (data['param']['param'] === 'list_motif_combination') {
    title = '組合せ表示'
  }
  $('#' + this.tag).append(
    $('<h2>' + title + '</h2>')
  )

    // モティーフ切替ラジオボタン
    //
  if (data['param']['param'] === 'list_kinsei_kimono') {
    $('#' + this.tag).append(
      $('<p>モティーフ切り出し: ' +
        '<input type="radio" name="trim_switch" value="off">無効  ' +
        '<input type="radio" name="trim_switch" value="on">有効</p>' +
        '<p>文様拡張: ' +
        '<input type="radio" name="extend_switch" value="off">無効  ' +
        '<input type="radio" name="extend_switch" value="on">有効</p>'
        )
    )
    var inputval = (data['param']['motif'] === 'on') ? 'on' : 'off'
    $('input[name=\'trim_switch\'][value=\'' + inputval + '\']').attr('checked', 'checked')
    inputval = (data['param']['extend'] === 'on') ? 'on' : 'off'
    $('input[name=\'extend_switch\'][value=\'' + inputval + '\']').attr('checked', 'checked')

    if (data['param']['param'] === 'list_kinsei_kimono' && data['param']['extend'] === 'on') {
      $('input[name=\'trim_switch\']').attr('disabled', 'disabled')
    }
    /*
     *イベントハンドラ
     */
    $('input[name=trim_switch]:radio').change(function () {
      var radioValue = $(this).val()
      var uri = URI(window.location.href)
      if (radioValue === 'on') {
        uri.addQuery({'motif': 'on'})
        window.location.href = uri
      } else {
        uri.removeQuery({'motif': undefined})
        window.location.href = uri
      }
    })
    $('input[name=extend_switch]:radio').change(function () {
      var radioValue = $(this).val()
      var uri = URI(window.location.href)
      if (radioValue === 'on') {
        uri.addQuery({'extend': 'on'})
        window.location.href = uri
      } else {
        uri.removeQuery({'extend': undefined})
        window.location.href = uri
      }
    })
  }
  $('#' + this.tag).append($('<div id="content"></div>'))

  // for (var i = 0; i < data.items.length; i++) {
  var i = 0
  for (var val in data.items) { // eslint-disable-line
    $('#content').append(
      $('<ul/>').attr({'id': 'sorted_list_' + i, 'class': 'cf sorted_list'})
    )
    i++
  }
  // }

  i = 0
  var caller = this
  for (var key in data.items) {
    var syo = data.items[key][0]['小題']
    var str = ''
    $('#sorted_list_' + i).append(
      $('<h3>' + syo + '</h3>')
    )
    data.items[key].forEach(function (val, j) {
      /**
       * [if description]
       * サブタイトル(モティーフ)が存在する場合 かつ サブタイトルがまだ書かれていない かつ 適切な章であるとき、サブタイトルを書く
       */
      if (val.モティーフ !== undefined && val.モティーフ !== str) {
        str = val.モティーフ
        $('#sorted_list_' + i).append(
          $('<h4 class="cf">' + str + '</h4>')
        )
      }
      /**
       * [if description]
       * モティーフ領域情報がある場合はモティーフ領域を出力
       * そうでない場合はサムネイルを出力
       */
      var imgsrc
      if (window.location.hash === '#kinsei_kimono' && data['param']['motif'] === 'on' && val.屏風番号 in data.motif) {
        imgsrc = 'TrimmingMotif.php?kosode=' + val.屏風番号 + '&motif=' + data.motif[val.屏風番号][0]['モティーフ'] + '&id=0&size=256'
      } else {
        imgsrc = caller.imgpath + val.画像ファイル名 + '/thumbnail/thumbnail_' + val.画像ファイル名 + '.png'
      }

      var address
      if (data['param']['param'] === 'list_motif_combination') {
        var hyperlink = 'http://kosode-limited.tommylab.ynu.ac.jp/'
        address = hyperlink + 'KoByViewerZ/HDIBZ.html?imageNumber=' + val.画像ファイル名 + '&kosodeNumber=' + val.資料番号
        data.motif[val.資料番号].forEach(function (mval) {
          if (val.小題 === mval.組合せ) {
            address += '&強調モティーフ=' + mval.モティーフ要素
          }
        })
      } else {
        address = '#' + val.屏風番号
      }
      caller._writeImage('sorted_list_' + i, address, {'src': imgsrc, 'height': '128', 'width': '128'}, val.屏風番号)
    })
    i++
  }
}
