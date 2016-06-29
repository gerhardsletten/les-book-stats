import React, {Component, PropTypes} from 'react'
import RC2 from 'react-chartjs2'
import randomColor from 'random-color'
import Colr from 'colr'
import {color, lightness} from 'kewler'
import style from './App.less'

const uniq = (arrArg) => {
  return arrArg.filter((elem, pos, arr) => arr.indexOf(elem) == pos)
}

class App extends Component {
  render () {
    const {general, stats} = this.props.data;
    return (
      <div>
        <div className={style.container}>
          <h3>{general.label}</h3>
          {this.renderChartGeneral(general.stats)}
        </div>
        {stats.map((dat, i) => {
          return (
            <div className={style.container} key={i}>
              <h3>{dat.label}</h3>
              {this.renderChart(dat.stats)}
            </div>
          )
        })}
      </div>
    )
  }

  renderChart (stats) {
    const years = uniq(stats.map((stat) => stat.year)).sort((a,b) => a > b)
    const values = uniq(stats.map((stat) => stat.value))
    const data = {
      labels: years,
      datasets: values.map((value, i) => {
        const randColor = randomColor().hexString()
        const fgColor = color(randColor)
        const bgColor = fgColor(lightness(-10))
        return Object.assign({}, this.defaultLine(this.randColor()), {
          label: `${value}`,
          data: years.map((year) => {
            const found = stats.filter((stat) => stat.year === year && stat.value === value)
            return found.length;
          })
        })
      })
    }
    return (
      <RC2 data={data} type='line' />
    )
  }

  renderChartGeneral (stats) {
    const years = uniq(stats.map((stat) => stat.year)).sort((a,b) => a > b)
    const data = {
      labels: years,
      datasets: [Object.assign({}, this.defaultLine(this.randColor()), {
        label: 'År',
        data: years.map((year) => {
          const found = stats.find((stat) => stat.year === year)
          return found.value;
        })
      })]
    }
    return (
      <RC2 data={data} type='line' />
    )
  }

  randColor () {
    return randomColor().hexString()
  }

  defaultLine (aColor = '#333') {
    const fgColor = color(aColor)
    const bgColor = fgColor(lightness(-10))
    return {
      label: 'Label',
      fill: false,
      lineTension: 0.1,
      backgroundColor: fgColor(),
      borderColor: bgColor(),
      borderCapStyle: 'butt',
      borderDash: [],
      borderDashOffset: 0.0,
      borderJoinStyle: 'miter',
      pointBorderColor:  bgColor(),
      pointBackgroundColor: fgColor(),
      pointBorderWidth: 1,
      pointHoverRadius: 5,
      pointHoverBackgroundColor: fgColor(),
      pointHoverBorderColor: fgColor(),
      pointHoverBorderWidth: 1,
      pointRadius: 5,
      pointHitRadius: 20,
      data: []
    }
  }
}

App.propTypes = {
  data: PropTypes.object
}

export default App
