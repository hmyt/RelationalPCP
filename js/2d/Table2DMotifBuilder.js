/* globals $, Table2D, Table2DBuilder */

function inherits (childCtor, parentCtor) {
  Object.setPrototypeOf(childCtor.prototype, parentCtor.prototype)
}

function Table2DMotifBuilder (data, Attributes) {
  this.table2d = new Table2D(data, Attributes)
  this.table2d.setYearSet([
    '1600',
    '1650',
    '1700',
    '1750',
    '1800'
  ])
  this.list = {}
  this.colList
}

inherits(Table2DMotifBuilder, Table2DBuilder)

/**
 * @override
 * [_generateImageTagbyHash description]
 * @param  {string} k [横軸の属性値]
 * @param  {Integer} i [順番]
 * @return {jQueryDomObject}   [画像サムネイルの出力]
 */
Table2DMotifBuilder.prototype._generateImageTagbyHash = function (k, i) {
  var caller = this
  var jqueryObject = $('<div/>').attr('style', 'width: 120px; float:left;').append(
    $('<a/>').attr('href', 'index.html#' + this.list[k][i]['屏風番号']).append(
      $('<img/>').attr({
        'src': 'TrimmingMotif.php?kosode=' + this.list[k][i]['資料番号'] + '&motif=' + k + '&id=0&size=100',
        'alt': k,
        'width': '100',
        'height': '100'
      })
    )
  ).append(function () {
    var buffer = ''
    for (var j = 0; j < caller.table2d.data.tech[caller.list[k][i]['資料番号']].length; j++) {
      buffer += caller.table2d.data.tech[caller.list[k][i]['資料番号']][j]['技法'] + ','
    }
    buffer = buffer.slice(0, -1)
    return '<div class="tips">' + buffer + '</div>'
  })

  return jqueryObject
}
