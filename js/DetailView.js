/* globals $, OpenSeadragon */
function DetailView (tag, imgDir) {
  this.tag = tag
  this.imgDir = imgDir
}

var detailTabsList = {
  'items': {'id': 'kanzoudb', 'tabtitle': '館蔵コレ'},
  'kaidai': {'id': 'kaidai', 'tabtitle': '解題'},
  'low': {'id': 'low', 'tabtitle': 'LoW'},
  'hinagata': {'id': 'hinagata', 'tabtitle': '雛形'}
}

DetailView.prototype.generateHtmlID = function (id) {
  return id.replace(/[ !"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~]/g, '') //eslint-disable-line
}

DetailView.prototype.display = function (data) {
  $('#' + this.tag)

  $('#' + this.tag)
    .append(
      $('<div/>').attr('id', 'content_viewer').addClass('content_vw').css({'height': '500px'})
      )
    .append(
      $('<div/>').attr({'id': 'detail_tabs'})
      .append(
        $('<ul/>')
      )
  )

  for (var key in data) {
    // タブの追加
    $('#detail_tabs ul').append(
      $('<li/>').append(
        $('<a>' + detailTabsList[key]['tabtitle'] + '</a>').attr({'href': '#' + detailTabsList[key]['id']})
      )
    )
    // テーブルタグの追加
    $('#detail_tabs').append(
      $('<table/>').attr('id', detailTabsList[key]['id']).addClass('content_vw')
    )
  }

  for (var dataKey in data) {
    for (key in data[dataKey][0]) {
      if (key !== '画像ファイル名') {
        // テーブル内へtrタグの追加
        $('#' + detailTabsList[dataKey]['id']).append(
            $('<tr/>').attr({'id': detailTabsList[dataKey]['id'] + '_' + this.generateHtmlID(key)})
          )
      }
    }
  }

  // // Zoomify出力
  // imageNum = data.items[0].画像ファイル名 // eslint-disable-line
  // Z.showImage('content_viewer', this.imgpath + imageNum, {'zTilesPNG': '1', 'zInitialZoom': '3', 'zInitialX': '10000', 'zInitialY': '8000'})

  showOpenSeadragon(this.imgDir, data.items[0].画像ファイル名)

  /**
   * [for description]
   * 詳細情報表示
   */
  // var i = 0;
  for (dataKey in data) {
    for (key in data[dataKey][0]) {
      if (key !== '画像ファイル名') {
        // テーブル内へthタグの追加
        $('#' + detailTabsList[dataKey]['id'] + '_' + this.generateHtmlID(key)).append(
          $('<th>' + key + '</th>').addClass('context')
        )
        // i++;
      }
    }
  }
  for (dataKey in data) {
    for (var i = 0; i < data[dataKey].length; i++) {
      // var j = 0;
      for (key in data[dataKey][i]) {
        /**
         * [if description]
         * stringならばそのまま出力
         * そうでない場合は配列になっているはずなので、展開してカンマ区切りで連結したstringを出力する
         */
        if (typeof data[dataKey][i][key] === 'string') {
          if (key !== '画像ファイル名') {
            var str = data[dataKey][i][key]
          }
        } else {
          str = ''
          for (var j = 0; j < data[dataKey][i][key].length; j++) {
            if (str !== '') {
              str += ','
            }
            str += data[dataKey][i][key][j]
          }
        }
        $('#' + detailTabsList[dataKey]['id'] + '_' + this.generateHtmlID(key)).append($('<td>' + str + '</td>').addClass('context'))
      }
      // j++;
    }
  }
  $('#detail_tabs').tabs()
}

function showOpenSeadragon (imgDir, imgFilename) {
  var imgPath = imgDir + imgFilename
  var imgInfo = {
    width: 32768,
    height: 32768,
    tileHeight: 256,
    tileWidth: 256,
    fileAmount: 21745
  }
  var tileSource = {
    width: imgInfo.width,
    height: imgInfo.height,
    tileSize: imgInfo.tileWidth,
    minLevel: 0,
    maxLevel: 7,
    getTileUrl: function (level, x, y) {
      return imgPath + '/zoomlevel' + level + '/zoomlevel' + level + '_x' + x + '_y' + y + '_' + imgFilename + '.png'
    }
  }
  var viewer = OpenSeadragon({
    defaultZoomlevel: 1.6,
    id: 'content_viewer',
    prefixUrl: './js/vendor/openseadragon/images/'
  })

  viewer.addTiledImage({
    tileSource: tileSource,
    width: 1,
    success: function (e) {
      var image = e.item
      image.setPosition({x: 0.20, y: 0.26}, true)
    }
  })
}
