import { useState } from 'react'
import './App.css'
import { Button } from './components/ui/button'
import { Textarea } from './components/ui/textarea'
import './index.css'
import Markdown from 'react-markdown'
import { fetchEventSource } from '@microsoft/fetch-event-source'
import { getApiURL } from './lib/axios'
function App() {

  const [prompt, setPrompt] = useState('')
  const [answer, setAnswer] = useState('Try queries like: "affordave dive watch", "dress watch with a black dial", "more affordable alternative to a Rolex submariner", ...')
  const [isStreaming, setIsStreaming] = useState(false)

  const submitForm = () => {
    newStreamResponse(prompt)
  }

  const newStreamResponse = async (prompt :string) => {
        const ctrl = new AbortController()
        setAnswer('')

        let messageContent = ''
        setIsStreaming(true)

        await fetchEventSource(`${getApiURL()}/api/watch`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': '*/*',
            },
            openWhenHidden: true,
            body: JSON.stringify({
              'query': prompt,
            }),

            onmessage(ev) {
                const tmp = JSON.parse(ev.data)

                if (tmp.chunk !== null) {
                    messageContent += tmp.chunk
                    setAnswer(messageContent)
                }
            },
            onclose() {
                // do not retry
                ctrl.abort()
            },
            onerror(err) {
                console.log(err)
                // toast.error('Something unexpected happened.', {
                //     position: toast.POSITION.BOTTOM_RIGHT,
                //     toastId: 'prompt-error',
                // })
                ctrl.abort()
                throw err;
            },
            signal: ctrl.signal,
        }).then(() => {
            setIsStreaming(false)
        })
    }

  return (
    <>
      <div className="grid w-full gap-2">
        <h1 className="text-4xl mb-8 text-center">⌚ Watch Finder</h1>
        <Textarea 
          onChange={(e) => setPrompt(e.target.value)} 
          placeholder="Describe the watch you are looking for..." 
          value={prompt} />
        <Button 
          onClick={() => submitForm()}
          disabled={isStreaming || prompt === ''}
          >Find a watch!</Button>

        <div className="p-4 rounded-sm my-8 bg-gray-100 prose lg:prose-md w-full max-w-max text-left">
          <Markdown>{answer}</Markdown>
        </div>
      </div>
    </>
  )
}

export default App
