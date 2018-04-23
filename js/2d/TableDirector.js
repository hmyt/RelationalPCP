function TableDirector (builder) {
  this.builder = builder
}

TableDirector.prototype.constract = function () {
  this.builder.mapBasedOnRow()
  this.builder.writeThead('#wrapper')
  this.builder.writeCells()
}
