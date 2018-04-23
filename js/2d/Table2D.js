function Table2D (data, attributes) {
  this.yearset = [
    '1550',
    '1600',
    '1650',
    '1700',
    '1750',
    '1800'
  ]
  this.data = data
  this.Attributes = attributes
  this.yearKey = this.getYearKeys()
}

Table2D.prototype.setYearSet = function (yearset) {
  this.yearset = yearset
  this.yearKey = this.getYearKeys()
}

Table2D.prototype.getYearKeys = function () {
  var yearKey = []
  for (var i = 0; i < this.yearset.length - 1; i++) {
    yearKey.push({'年代': this.yearset[i] + '-' + this.yearset[i + 1]})
  }
  return yearKey
}
