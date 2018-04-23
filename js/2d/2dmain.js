/* globals $, URI, Table2DBuilder, TableDirector */

$(function () {
// ////////////////////////////
// //////// Entry Point ///////
// ////////////////////////////

  // URLパラメータから縦・横軸の属性を取り出す
  var uriParam = URI.parseQuery(URI(window.location.href).query())
  if (uriParam.row == null) {
    uriParam.row = 'motif'
  }
  if (uriParam.col == null) {
    uriParam.col = 'year'
  }
  var title = {}
  titlefetcher().done(function (d) {
    title.row = d[uriParam.row]
    title.col = d[uriParam.col]
    $('#subtitle').text(title.row + '-' + title.col + '表示')
  })

  // データ取得および画面描画
  writeAjaxPage()

  // プルダウンメニュー初期化
  $("select[name='row']").val(uriParam.row)
  $("select[name='col']").val(uriParam.col)
  $("select[name='row'] option[value='" + uriParam.col + "']").attr('disabled', true)
  $("select[name='col'] option[value='" + uriParam.row + "']").attr('disabled', true)

  // プルダウンメニューのイベントハンドラ
  $('select').change(function () {
    changeSelectFormDisabled(this)
  })

  // プルダウンメニューの「交換」ボタンのイベントハンドラ
  $("input:button[value='交換']").click(function () {
    var rowVal = $("select[name='row'] option:selected").val()
    var colVal = $("select[name='col'] option:selected").val()
    $("select[name='col']").val(rowVal).change()
    $("select[name='row']").val(colVal).change()
  })

// /////////////////////////////
// //////// Subroutine ///////
// ////////////////////////////

  // プルダウンメニューのdisabled属性の変更する関数
  function changeSelectFormDisabled (select) {
    $(select)
      .siblings('select')
      .children('option[value=' + select.value + ']')
      .attr('disabled', true)
      .siblings().removeAttr('disabled')
  }

  // パラメータとタイトルの対応関係取得する関数
  function titlefetcher () {
    return $.ajax({
      url: './json/param_list.txt',
      dataType: 'json'
    })
  }

  // データ取得および画面描画の関数
  function writeAjaxPage () {
    $('#wrapper').empty()
    $.ajax({
      url: './json/json_2d.php?row=' + uriParam.row + '&col=' + uriParam.col,
      dataType: 'json'
    }).done(function (data) {
      var builder = new Table2DBuilder(data, title)
      var dir = new TableDirector(builder)
      dir.constract()

      // マウスオーバー時ハイライト
      var overcells = $('table td')
      var hoverClass = 'hover'
      var currentRow
      var currentCol

      overcells.hover(
        function () {
          var $this = $(this)
          currentRow = $this.parent().children('table td')
          currentRow.addClass(hoverClass)
          currentCol = overcells.filter(':nth-child(' + (currentRow.index($this) + 1) + ')')
          currentCol.addClass(hoverClass)
        },

        function () {
          currentRow.removeClass(hoverClass)
          currentCol.removeClass(hoverClass)
        }
      )

      $('table').floatThead({position: 'absolute'})
    }).fail(function (jqXHR, textStatus, errorThrown) {
      window.alert('Error : ' + errorThrown)
    })
  }
})
