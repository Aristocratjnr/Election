(() => {
  const getOrCreateLegendList = (chart, id) => {
    const legendContainer = document.getElementById(id);
    let listContainer = legendContainer.querySelector('ul');

    if (!listContainer) {
      listContainer = document.createElement('ul');
      listContainer.classList.add('custom-legend-ul');
      legendContainer.appendChild(listContainer);
    }

    return listContainer;
  };

  const htmlLegendPlugin = {
    id: 'htmlLegend',
    afterUpdate(chart, args, options) {
      const ul = getOrCreateLegendList(chart, options.containerID);

      while (ul.firstChild) {
        ul.firstChild.remove();
      }

      const items = chart.options.plugins.legend.labels.generateLabels(chart);

      items.forEach(item => {
        const li = document.createElement('li');
        li.classList.add('custom-legend-li');

        li.onclick = () => {
          chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
          chart.update();
        };
        const boxSpan = document.createElement('span');
        boxSpan.classList.add('legend-box');
        boxSpan.style.backgroundColor = item.fillStyle;
        const textContainer = document.createElement('span');
        textContainer.textContent = item.text;
        textContainer.style.textDecoration = item.hidden ? 'line-through' : '';

        li.appendChild(boxSpan);
        li.appendChild(textContainer);
        ul.appendChild(li);
      });
    }
  };

  window.LegendUtils = {
    getOrCreateLegendList,
    htmlLegendPlugin
  };
})();
