class PhpSvgCharts {

  static EVENT_POINTER_DATACHANGE_XAXIS = 'svgcharts_pointer_datachange_xaxis'
  static EVENT_POINTER_POSITIONCHANGE_XAXIS = 'svgcharts_pointer_positionchange_xaxis'

  static bindPointerEvents (svgElements) {
    if (svgElements instanceof Element) {
      svgElements = [svgElements]
    }
    for (const svgElement of svgElements) {
      let lastW = null
      let lastH = null
      let cache = {}

      svgElement.addEventListener('pointermove', (ev) => {
        const domRect = svgElement.getBoundingClientRect()
        const width = parseFloat(svgElement.getAttribute('width'))
        const height = parseFloat(svgElement.getAttribute('height'))
        if (width !== lastW || height !== lastH) {
          lastW = width
          lastH = height
          cache = {}
          cache.seriesGroup = svgElement.querySelector('g[data-renderer-id="series"]')
          cache.xValuesGrouped = []
          cache.lastMatchedData = null
          const labels = cache.seriesGroup.querySelectorAll('[data-value-x-label]')
          const group = {}
          for (const labelEl of labels) {
            const xValue = labelEl.getAttribute('data-value-x')
            const xLabel = labelEl.getAttribute('data-value-x-label')
            const xPos = parseFloat(labelEl.getAttribute('cx'))
            if (!group[xPos]) {
              group[xPos] = []
            }
            group[xPos] = {
              xLabel, xValue, xPos,
            }
          }
          cache.xValuesGrouped = Object.values(group).sort((a, b) => {
            if (a.xPos > b.xPos) {
              return 1
            } else if (a.xPos < b.xPos) {
              return -1
            } else {
              return 0
            }
          })

        }
        const pointerPos = {
          svgX: width * (1 / domRect.width * ev.offsetX),
          svgY: height * (1 / domRect.height * ev.offsetY),
        }

        let matchedData = null
        for (const row of cache.xValuesGrouped) {
          const diff = Math.abs(row.xPos - pointerPos.svgX)
          if (!matchedData || matchedData.diff > diff) {
            matchedData = { diff, row }
          }
          if (diff > matchedData.diff) {
            break
          }

        }
        if (!cache.lastMatchedData || cache.lastMatchedData.row.xPos !== matchedData.row.xPos) {
          cache.lastMatchedData = matchedData
          const event = new CustomEvent(this.EVENT_POINTER_DATACHANGE_XAXIS, matchedData.row)
          svgElement.dispatchEvent(event)
        }
      })
    }
  }
}