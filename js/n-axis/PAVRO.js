/* global d3, URI, PCModules, _, $ */

function PAVRO (param) {
  this.config = {
    data: [],
    showThumbnails: false,
    thumbnailPaths: {},
    joinableAxis: [],
    jsonAPI: '',
    defaultAxis: '',
    enableDate: false,
    dateFrom: '',
    dateTo: ''
  }
  _.extend(this.config, param)
}

PAVRO.prototype.generateParCoords = function () {
  // カラースケール(固定領域)
  var stringColorscale = d3.scale.ordinal()
    .range(['#3182bd', '#e6550d', '#31a354',
      '#e377c2', '#fd8d3c', '#17becf',
      '#fdae6b',
      '#74c476', '#9467bd',
      '#ff9896', '#a1d99b', '#9ecae1'])
  // var blue_to_brown = d3.scale.linear()
  // .domain([1600, 1650, 1700, 1750])
  // .range(['#3498db', '#2ecc71', '#f1c40f', '#e74c3c'])
  // .interpolate(d3.interpolateLab);

  // カラースケール(標準偏差)
  var zcolorscale = d3.scale.linear()
  .domain([-2, -0.5, 0.5, 2])
  .range(['#3498db', '#2ecc71', '#f1c40f', '#e74c3c'])
  .interpolate(d3.interpolateLab)

  var colorscale = {
    'stringColorscale': stringColorscale,
    'zcolorscale': zcolorscale
  }

  var disabledAxis = this.config.data['disabledAxis']
  var hidedAxises = disabledAxis.concat(this.config.joinableAxis)
  var numericAxises = this.config.data['numericAxises']
  var outerjoinableAxises = this.config.data['outerjoinable']
  var outerjoined = []
  var toggleList = _.difference(d3.keys(this.config.data['items'][0]).concat(this.config.joinableAxis), disabledAxis)
  var pcmod = new PCModules({
    colorscale: colorscale,
    numericAxises: numericAxises,
    thumbnailPaths: this.config.thumbnailPaths,
    showThumbnails: this.config.showThumbnails
  })

  // Parallel Coordinates生成
  var pc = d3.parcoords({nullValueSeparator: 'bottom'})('#graph')
  .data(this.config.data['items'])
  .hideAxis(hidedAxises)
  // .color(function(d) { return blue_to_brown(d['年代']); })
  .alpha(0.4)
  .composite('darken')
  .mode('queue')
  // .smoothness(.2) // require sylvester.js
  .render()
  .createAxes()
  .brushMode('1D-axes-multi')
  .reorderable() // 軸の並べ替えを可能にする
  .interactive()

  // イベントハンドラ登録
  pcmod.init_color(pc, pcmod)

  // 色の初期値は「年代」を基準とする
  pcmod.change_color(pc, this.config.defaultAxis)

  var caller = this
  // 軸表示数変更
  d3.select('#switch_axis')
    .html('')
    .selectAll('.row')
    .data(toggleList)
    .enter()
    .append('div')
    .on({
      'click': toggleAxis,
      'mouseenter': mouseEnterDropdownMenu,
      'mouseleave': mouseLeaveDropdownMenu
    })
    .each(function (d) {
      var list = d3.select(this)
      caller.config.joinableAxis.forEach(function (key) {
        if (key === d) {
          list.attr('class', 'hide_axis external_' + key)
        }
      })
      outerjoinableAxises.forEach(function (key) {
        if (key === d) {
          list.append('div')
            .attr('class', 'outerjoin hide_axis external_' + key)
            .on('click', toggleOuterjoinable)
            .text('外部結合')
        }
      })
    })
    .append('span')
    .text(function (d, i) { return d })

  // データテーブル作成
  pcmod.generate_lists(pc, this.config.data['items'])

  // データテーブルの更新
  pc.on('brush', function (d) { pcmod.generate_lists(pc, d) })

  // and/or検索切替
  d3.select('#select_andor')
    .on('change', function (d) {
      pc.brushPredicate(this.value).render()
    })

  // 日付変更機能
  if (this.config.enableDate) {
    d3.select('#button_dateApply')
    .on({'click': () => { changeDataset() }})
  }

  // 軸の表示＆非表示を切り替える関数
  function toggleAxis (d) {
    if (hidedAxises.includes(d)) {
      d3.select(this).classed('show_axis', true)
      d3.select(this).classed('hide_axis', false)
      hidedAxises = _.difference(hidedAxises, [d])

      changeDataset()
    } else {
      if (outerjoined.includes(d)) {
        outerjoined = _.difference(outerjoined, [d])
      }
      d3.select(this).classed('hide_axis', true)
      d3.select(this).classed('show_axis', false)
      hidedAxises.push(d)
      changeDataset()
    }
  }

  function toggleOuterjoinable (d) {
    if (outerjoined.includes(d)) {
      outerjoined = _.difference(outerjoined, [d])
    } else {
      outerjoined.push(d)
    }
  }

  function mouseEnterDropdownMenu (d) {
    if (outerjoinableAxises.includes(d) && hidedAxises.includes(d)) {
      d3.select(this).select('div').style('display', 'block')
    }
  }
  function mouseLeaveDropdownMenu (d) {
    if (outerjoinableAxises.includes(d)) {
      d3.select(this).select('div').style('display', 'none')
    }
  }

  function changeDataset () {
    var parameter = _.difference(hidedAxises, disabledAxis)
    var url = new URI(this.config.jsonAPI)
    if (parameter.length > 0) {
      url.setQuery('exclude[]', parameter)
    }
    if (outerjoined.length > 0) {
      url.setQuery('outerjoin[]', outerjoined)
    }
    if (this.config.enableDate) {
      $('#datepicker').append('<div class="spinner-donut"></div>')
      this.config.dateFrom = $('#datepicker_from').val()
      this.config.dateTo = $('#datepicker_to').val()
      url.setQuery('date_from', this.config.dateFrom)
      url.setQuery('date_to', this.config.dateTo)
    }
    d3.json(url.toString(), function (dataFromServer) {
      this.config.data = dataFromServer
      pc.data(this.config.data['items']).hideAxis(hidedAxises).render().updateAxes()
      pcmod.init_color(pc, pcmod)
      pcmod.generate_lists(pc, this.config.data['items'])
      if (this.config.enableDate) {
        $('#datepicker div.spinner-donut').remove()
      }
    })
  }
}
