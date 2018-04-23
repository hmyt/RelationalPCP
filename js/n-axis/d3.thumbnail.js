/* globals d3 */
/**
 * 屏風サムネイル生成関数 for D3.js
 * @param {D3_DOM_Object} selection d3のdomオブジェクト
 *
 * --How to use
 * d3.select('#grid')
 *  .datum(d)
 *  .call(d3.thumbs())
 * 以上のように呼び出す.
 *
 * --assumed input dataset
 * [
 *   {'番号': {
                '資料番号': 'xxx',
                '屏風番号': 'yyy'
              },
 *    '画像ファイル名': 'zzz'
 *   }
 *    :
 *    :
 * ]
 *
 * --output html tags
 * <div class="byobu_cell">
 *  <div class="byobu-0 byobu">【xxx】</div>
 *  <div class="byobu-1 byobu">
 *    <a target="_blank" href="./index.html#【yyy】">
 *      <img src="【略】" height="128" width="128">
 *    </a>
 *  </div>
 * </div>
 *    :
 *    :
 */
d3.thumbs = function (config) {
  var columns = []

  var th = function (selection) {
    if (columns.length === 0) columns = d3.keys(selection.data()[0][0])

    // 屏風cells
    var cells = selection.selectAll('.byobu_cell')
        .data(function (d) { return d })

    cells.enter()
        .append('div')
        .attr('class', 'byobu_cell')

    cells.exit().remove()

    var byobus = selection.selectAll('.byobu_cell').selectAll('.byobu')
        .data(function (d) { return columns.map(function (col) { return d[col] }) })

    // 屏風番号 & サムネイル画像のためのタグ追加
    byobus.enter().append('div')
      .attr('class', function (d, i) { return 'byobu-' + i })
      .classed('byobu', true)
      .append('a')
      .attr('target', '_blank')
        .append('img')
        .attr('height', '128')
        .attr('width', '128')
    byobus.exit().remove()

    // 屏風番号
    selection.selectAll('.byobu_cell').select('.byobu-0')
      .text(function (d) { return d['番号']['資料番号'] })

    // サムネイル画像
    selection.selectAll('.byobu_cell').select('.byobu-1')
      .select('a')
      .attr('href', function (d) { return './index.html#' + d['番号']['屏風番号'] })
        .select('img')
        .attr('src', function (d) { return './images/高精細画像201404/' + d['画像ファイル名'] + '/thumbnail/thumbnail_' + d['画像ファイル名'] + '.png' })

    return th
  }

  th.columns = function (_) {
    if (!arguments.length) return columns
    columns = _
    return this
  }

  return th
}
