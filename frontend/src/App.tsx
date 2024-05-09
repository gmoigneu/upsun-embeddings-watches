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
  const [answer, setAnswer] = useState('')
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
        <Textarea 
          onChange={(e) => setPrompt(e.target.value)} 
          placeholder="Describe the watch you are looking for..." 
          value={prompt} />
        <Button onClick={() => submitForm()}>Send!</Button>

        {isStreaming && (
          <div className="flex justify-center">Streaming response...</div>
        )}
        <div className="flex">
          <div className="p-2 rounded-sm bg-grey-200 w-full text-left">
            <Markdown>{answer}</Markdown>
          </div>
        </div>
      </div>
    </>
  )
}

export default App
