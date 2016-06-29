require('babel-polyfill')
import React from 'react'
import {render} from 'react-dom'
import App from './App'

render((
  <App data={global.lesBookStats} />
), document.getElementById('les-book-stats-main'))
