import Axios from 'axios'

export const getApiURL = () => {
    if(window.location.hostname === 'watches.test') {
        return('http://api.watches.test')
    } else {
        return window.location.origin.replace('frontend', 'api')
    }
}


let entrypoint = getApiURL()
if(!entrypoint) {
    entrypoint = 'localhost'
}

const axios = Axios.create({
    baseURL: entrypoint,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
    },
})
export default axios