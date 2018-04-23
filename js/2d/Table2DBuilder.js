/* globals $, Table2D, TableBuilder, getImageDir */

function inherits (childCtor, parentCtor) {
  Object.setPrototypeOf(childCtor.prototype, parentCtor.prototype)
}

function Table2DBuilder (data, Attributes) {
  this.table2d = new Table2D(data, Attributes)
  this.list = {}
  this.colList
}

inherits(Table2DBuilder, TableBuilder)

Table2DBuilder.prototype.mapBasedOnRow = function () {
  var d = this.table2d.data
  var row = this.table2d.Attributes.row
  var col = this.table2d.Attributes.col
  for (var i = 0; i < d.items.length; i++) {
    var key
    var val = {資料番号: d.items[i]['資料番号'], 屏風番号: d.items[i]['屏風番号'], 画像ファイル名: d.items[i]['画像ファイル名']}
    val[col] = d.items[i][col]

    if (row === '年代') {
      for (var j = 0; j < this.table2d.yearset.length - 1; j++) {
        if (d.items[i]['年代'] >= this.table2d.yearset[j] && d.items[i]['年代'] <= this.table2d.yearset[j + 1]) {
          key = this.table2d.yearKey[j]['年代']
          break
        }
      }
    } else {
      key = d.items[i][row]
    }

    if (this.list[key] == null) {
      this.list[key] = []
    }
    this.list[key].push(val)
  }
}

Table2DBuilder.prototype.writeThead = function (appendtag) {
  var col = this.table2d.Attributes.col

  $(appendtag).append(
    $('<table/>').attr('id', 'content')
  )

  var caller = this
  $('#content').append(
    $('<thead>').append(
      $('<tr/>').attr('class', 'context theader').append(function () {
        var buffer = ''
        buffer += "<td class ='" + col + "_0 head_col'></td>"
        caller.colList = (col === '年代') ? caller.table2d.yearKey : caller.table2d.data.list.concat()
        caller.colList.forEach(function (val, index) {
          buffer += "<td class ='" + col + '_' + index + "'>" + val[col] + '</td>'
        })
        return buffer
      })
    )
  )
}

Table2DBuilder.prototype.writeCells = function () {
  var caller = this
  var col = this.table2d.Attributes.col
  for (var k in this.list) {
    $('#content').append(
        $('<tr/>').attr('class', 'context').append(function () {
          var buffer = ''
          buffer += "<td class ='" + k.replace(/\(/g, '（').replace(/\)/g, '）') + "_0 head_col'>" + k + '</td>'

          for (var i = 1; i <= caller.colList.length; i++) {
            buffer += "<td class ='" + k.replace(/\(/g, '（').replace(/\)/g, '）') + '_' + i + "'></td>"
          }
          return buffer
        })
      )
    for (var i = 0; i < this.list[k].length; i++) {
      var clsNo
      if (col === '年代') {
        for (var j = 0; j < this.table2d.yearset.length - 1; j++) {
          if (this.list[k][i][col] >= this.table2d.yearset[j] && this.list[k][i][col] <= this.table2d.yearset[j + 1]) {
            clsNo = j + 1
            break
          }
        }
      } else {
        for (j = 0; j < this.colList.length; j++) {
          if (this.colList[j][col] === this.list[k][i][col]) {
            clsNo = j + 1
            break
          }
        }
      }
      $('.' + k.replace(/\(/g, '（').replace(/\)/g, '）') + '_' + clsNo).append(this._generateImageTagbyHash(k, i))
    }
  }
}

Table2DBuilder.prototype._generateImageTagbyHash = function (k, i) {
  var jqueryObject = $('<a/>').attr('href', 'index.html#' + this.list[k][i]['屏風番号']).append(
    $('<img/>').attr({
      'src': getImageDir(window.location.hostname) + '/高精細画像201404/' + this.list[k][i]['画像ファイル名'] + '/thumbnail/thumbnail_' + this.list[k][i]['画像ファイル名'] + '.png',
      'height': '32',
      'width': '32',
      'alt': k
    })
  )

  return jqueryObject
}
