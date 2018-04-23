/* global d3, URI, PAVRO, confirmDatePlugin $ */

// データ定義
var config = {
  data: [], // ParallelCoordinatesの表示に使用するデータ
  showThumbnails: false,
  thumbnailPaths: {},
  joinableAxis: [],
  jsonAPI: '',
  defaultAxis: '',
  enableDate: false,
  dateFrom: '',
  dateTo: ''
}

$('#tabs').tabs()

var jsonUri
var uriParam = URI.parseQuery(URI(window.location.href).query())

// 初期値の定義
if (uriParam['param'] === 'seecon' || uriParam['param'] === 'seecon_intro_est') {
  var dateFrom = '2014-01-01 00:00'
  var dateTo = '2016-12-31 23:59'
  config.jsonAPI = uriParam['param'] === 'seecon' ? './json/json_seecon.php' : './json/json_seecon_introduction_estimates.php'
  config.defaultAxis = 'BuildingDemandEnergy'
  config.enableDate = true
  config.dateFrom = dateFrom
  config.dateTo = dateTo
  $('#datepicker').html(
    '<label id="label_datepicker" for="datepicker">Date:</label>' +
    '<input type="text" id="datepicker_from"> - <input type="text" id="datepicker_to">' +
    '<button id="button_dateApply">適用</button>'
  )
  // datepickerの初期化
  $('#datepicker_from').flatpickr({
    defaultDate: dateFrom,
    enableTime: true,
    plugins: [new confirmDatePlugin({})]
  })
  $('#datepicker_to').flatpickr({
    defaultDate: dateTo,
    enableTime: true,
    plugins: [new confirmDatePlugin({})]
  })

  $('#radio_table').prop('checked', true)
} else if (uriParam['param'] === 'ecolog') {
  config.jsonAPI = './json/json_ecolog.php'
  config.defaultAxis = 'SEMANTIC_LINK_ID'
} else {
  config.jsonAPI = './json/json_n-axis.php'
  config.defaultAxis = '年代'
  config.joinableAxis = ['モード', '解説文_技法', '分類', 'モティーフ', '近世きもの万華鏡', '章']
  config.showThumbnails = true

  $('#radio_thumbnail').prop('checked', true)
}

jsonUri = new URI(config.jsonAPI)

// urlパラメータ定義
if (uriParam['param'] === 'kissyo') {
  jsonUri.setQuery('param', 'kissyo')
} else {
  if (config.joinableAxis.length !== 0) {
    jsonUri.setQuery('exclude[]', config.joinableAxis)
  }
  if (uriParam['param'] === 'seecon') {
    jsonUri.setQuery('date_from', dateFrom)
    jsonUri.setQuery('date_to', dateTo)
  }
}

// 処理実行
// 先にサムネイルのパスを取得しないと、PAVRO実行時にエラーが出てしまうので逐次処理をしている
Promise.resolve()
  .then(fetchThumbnailsUrlList)
  .then(executePavro)
  .catch(function (e) {
    console.log(e)
  })

// サムネイルのパスを取得
function fetchThumbnailsUrlList () {
  return new Promise((resolve, reject) => {
    d3.json('./json/json.php?param=list', (data) => {
      data.items.forEach((val, i) => {
        config.thumbnailPaths[val['屏風番号']] = val['画像ファイル名']
      })
    })
    resolve()
  })
}

// ParallelCoordinatesの描画に使用するJSONを取得、PAVRO処理実行
function executePavro () {
  return new Promise((resolve, reject) => {
    d3.json(jsonUri.toString(), (dataFromServer) => {
      config.data = dataFromServer
      var pavro = new PAVRO(config)
      pavro.generateParCoords()
    })
    resolve()
  })
}
