/* globals d3, _ */
/**
 * [PCModules description]
 * Parallel Coordinatesに使えそうなモジュール群を突っ込んだクラス
 *
 * 現在存在している機能:
 *     - 軸のカラーリング機能
 *     - テーブル＆サムネイル生成機能
 */
function PCModules (param) {
  this.config = {
    colorscale: param.colorscale,
    numericAxises: param.numericAxises,
    dataSetFilename: param.thumbnailPaths,
    showThumbnails: param.showThumbnails
  }
}

PCModules.prototype.init_color = function (pc, pcmod) {
  pc.svg.selectAll('.dimension')
    .on('click', function (d) { pcmod.change_color(pc, d) })
    .selectAll('.label')
    .style('font-size', '30px')
}

// カラーリング更新
PCModules.prototype.change_color = function (pc, dimension) {
  pc.svg.selectAll('.dimension')
  .style('font-weight', 'normal')
  .filter(function (d) { return d === dimension })
  .style('font-weight', 'bold')

  var colorFunc
  var d = pc.data()
  if (_.contains(this.config.numericAxises, dimension)) {
    colorFunc = this._zcolor(d, dimension)
  } else {
    var stringColorscale = this.config.colorscale.stringColorscale
    colorFunc = function (d) { return stringColorscale(d[dimension]) }
  }
  pc.color(colorFunc).render()
}

PCModules.prototype.generate_lists = function (pc, d) {
  this._generate_datatable(pc, d)
  if (this.config.showThumbnails) {
    this._generate_thumbnails(d)
  } else {
    d3.select('#num_of_data')
      .datum(d.length)
      .text(function (d) { return d + '件' })
  }
}

// 配列の重複を削除する関数
PCModules.prototype._trim_for_thumbs = function (d) {
  var byobus = (_.map(d, function (x) { return {'資料番号': x['資料番号'], '屏風番号': x['屏風番号']} }))
  var trimmedData = _.uniq(byobus, '資料番号')
  var byobuImgIds = []
  for (var i = 0; i < trimmedData.length; i++) {
    byobuImgIds.push({
      '番号': {
        '資料番号': trimmedData[i]['資料番号'],
        '屏風番号': trimmedData[i]['屏風番号']
      },
      '画像ファイル名': this.config.dataSetFilename[trimmedData[i]['屏風番号']]
    })
  }

  return byobuImgIds
}

// データテーブル生成関数
PCModules.prototype._generate_datatable = function (pc, d) {
  d3.select('#grid')
  .datum(d)
  .call(d3.divgrid())
  .selectAll('.row')
  .on({
    'mouseover': function (d) { pc.highlight([d]) },
    'mouseout': pc.unhighlight,
    'click': function (d) { window.open('./index.html#' + d['屏風番号'], '_blank') }
  })
}

// サムネイル生成関数
PCModules.prototype._generate_thumbnails = function (d) {
  var thumbsList = this._trim_for_thumbs(d)

  d3.select('#num_of_data')
    .datum(thumbsList.length)
    .text(function (d) { return '該当小袖数：' + d + '着' })

  d3.select('#thumbnails')
    .datum(thumbsList)
    .call(d3.thumbs())
}

// プロットと軸名に基づいたcolor関数を返す関数
PCModules.prototype._zcolor = function (col, dimension) {
  var z = this._zscore(_.pluck(col, dimension).map(parseFloat))  // this is function
  var zcolorscale = this.config.colorscale.zcolorscale
  return function (d) { return zcolorscale(z(d[dimension])) }
}

// 標準偏差生成関数
PCModules.prototype._zscore = function (col) {
  var mean = _.mean(_.without(col, NaN))
  var sigma = _.stdDeviation(_.without(col, NaN))
  // console.log('sigma'+sigma+'mean'+mean+'a::'+_.without(col, NaN))
  return function (d) {
    return (d - mean) / sigma // dの偏差値を求める
  }
}
