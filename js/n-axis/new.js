/* global d3, URI, PCModules, _ */

class PAVRO {
  constructor(param) {
    this.config = {
      data: [],
      showThumbnails: false,
      thumbnailPaths: {},
      joinableAxis: [],
      jsonAPI: '',
      defaultAxis: '',
      enableDate: false
    }
    _.extend(this.config, param)
  }

  generateParCoords() {
    // カラースケール(固定領域)
    const stringColorscale = d3.scale.category10();
    // var blue_to_brown = d3.scale.linear()
    // .domain([1600, 1650, 1700, 1750])
    // .range(['#3498db', '#2ecc71', '#f1c40f', '#e74c3c'])
    // .interpolate(d3.interpolateLab);

    // カラースケール(標準偏差)
    const zcolorscale = d3.scale.linear()
    .domain([-2, -0.5, 0.5, 2])
    .range(['#3498db', '#2ecc71', '#f1c40f', '#e74c3c'])
    .interpolate(d3.interpolateLab);

    const colorscale = {
      'stringColorscale': stringColorscale,
      'zcolorscale': zcolorscale
    };

    const disabledAxis = this.config.data['disabledAxis'];
    let hidedAxises = disabledAxis.concat(this.config.joinableAxis);
    const numericAxises = this.config.data['numericAxises'];
    const outerjoinableAxises = this.config.data['outerjoinable'];
    let outerjoined = [];
    const toggleList = _.difference(d3.keys(this.config.data['items'][0]).concat(this.config.joinableAxis), disabledAxis);
    const pcmod = new PCModules(colorscale, numericAxises, this.config.thumbnailPaths);

    // Parallel Coordinates生成
    const pc = d3.parcoords({nullValueSeparator: 'bottom'})('#graph')
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
    .interactive();

    // イベントハンドラ登録
    pcmod.init_color(pc, pcmod)

    // 色の初期値は「年代」を基準とする
    pcmod.change_color(pc, this.config.defaultAxis)

    const caller = this;
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
        const list = d3.select(this);
        caller.config.joinableAxis.forEach(key => {
          if (key === d) {
            list.attr('class', 'hide_axis external_' + key)
          }
        })
        outerjoinableAxises.forEach(key => {
          if (key === d) {
            list.append('div')
              .attr('class', 'outerjoin hide_axis external_' + key)
              .on('click', toggleOuterjoinable)
              .text('外部結合')
          }
        })
      })
      .append('span')
      .text((d, i) => d)

    // データテーブル作成
    pcmod.generate_lists(pc, this.config.data['items'])

    // データテーブルの更新
    pc.on('brush', d => { pcmod.generate_lists(pc, d) })

    // and/or検索切替
    d3.select('#select_andor')
      .on('change', function (d) {
        pc.brushPredicate(this.value).render()
      })

    // 軸の表示＆非表示を切り替える関数
    function toggleAxis (d) {
      if (_.contains(hidedAxises, d)) {
        d3.select(this).classed('show_axis', true)
        d3.select(this).classed('hide_axis', false)
        hidedAxises = _.difference(hidedAxises, [d])

        changeDataset()
      } else {
        if (_.contains(outerjoined, d)) {
          outerjoined = _.difference(outerjoined, [d])
        }
        d3.select(this).classed('hide_axis', true)
        d3.select(this).classed('show_axis', false)
        hidedAxises.push(d)
        changeDataset()
      }
    }

    function toggleOuterjoinable (d) {
      if (_.contains(outerjoined, d)) {
        outerjoined = _.difference(outerjoined, [d])
      } else {
        outerjoined.push(d)
      }
    }

    function mouseEnterDropdownMenu (d) {
      if (_.contains(outerjoinableAxises, d) && _.contains(hidedAxises, d)) {
        d3.select(this).select('div').style('display', 'block')
      }
    }
    function mouseLeaveDropdownMenu (d) {
      if (_.contains(outerjoinableAxises, d)) {
        d3.select(this).select('div').style('display', 'none')
      }
    }

    function changeDataset () {
      const parameter = _.difference(hidedAxises, disabledAxis);
      const url = new URI(this.config.jsonAPI);
      if (parameter.length > 0) {
        url.setQuery('exclude[]', parameter)
        url.setQuery('outerjoin[]', outerjoined)
      }
      d3.json(url.toString(), function (dataFromServer) {
        this.config.data = dataFromServer
        pc.data(this.config.data['items']).hideAxis(hidedAxises).render().updateAxes()
        pcmod.init_color(pc, pcmod)
        pcmod.generate_lists(pc, this.config.data['items'])
      })
    }
  }
}
