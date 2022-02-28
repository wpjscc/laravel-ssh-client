<script setup>
import { Terminal } from 'xterm';
import { WebLinksAddon } from 'xterm-addon-web-links';
import { AttachAddon } from 'xterm-addon-attach';
import { FitAddon } from 'xterm-addon-fit';
import { SearchAddon } from 'xterm-addon-search';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import BreezeInput from '@/Components/Input.vue';
import BreezeLabel from '@/Components/Label.vue';
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';

import BreezeButton from '@/Components/Button.vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue';

import { nextTick ,ref} from 'vue'
var baseTheme = {
    foreground: '#F8F8F8',
    background: '#2D2E2C',
    selection: '#5DA5D533',
    black: '#1E1E1D',
    brightBlack: '#262625',
    red: '#CE5C5C',
    brightRed: '#FF7272',
    green: '#5BCC5B',
    brightGreen: '#72FF72',
    yellow: '#CCCC5B',
    brightYellow: '#FFFF72',
    blue: '#5D5DD3',
    brightBlue: '#7279FF',
    magenta: '#BC5ED1',
    brightMagenta: '#E572FF',
    cyan: '#5DA5D5',
    brightCyan: '#72F0FF',
    white: '#F8F8F8',
    brightWhite: '#FFFFFF'
  };
    var intervalId;

window.term = new Terminal({
    fontFamily: '"Cascadia Code", Menlo, monospace',
    theme: baseTheme,
    cols: 80,
    rows: 24,
    cursorBlink: true
});
term.setOption('logLevel', 'debug');

const searchAddon = new SearchAddon();
term.loadAddon(searchAddon);
window.searchAddon = searchAddon



const fitAddon = new FitAddon();
term.loadAddon(fitAddon);
window.fitAddon = fitAddon


term.loadAddon(new WebLinksAddon());
var wSocket;

const props = defineProps({
    ws: String,
});
const isConnected = ref()
const form = ref({
    idconnection: props.idconnection,
});

const authSsh = ()=>{
      wSocket = new WebSocket(props.ws);

    const attachAddon = new AttachAddon(wSocket);
    term.loadAddon(attachAddon);

    wSocket.onopen = function (event) {
        console.log("Socket Open");
        wSocket.send(JSON.stringify({
            sharessh: Object.assign({}, form.value)
        }));
        isConnected.value=true
        intervalId = window.setInterval(function(){
            wSocket.send(JSON.stringify({"refresh":""}));
        }, 700);
    };
     wSocket.onerror = function (event){
        console.error("Socket Error",event);
        isConnected.value=false

        window.clearInterval(intervalId);
    };
    wSocket.onclose = function (event){
        console.error("Socket onclose",event);
        isConnected.value=false

        window.clearInterval(intervalId);
    };
     nextTick(()=>{
        term.open(document.querySelector('.ssh'));
        fitAddon.fit()
        term.onData(e => {
            var dataSend = {"data":{"data":e}};
            wSocket.send(JSON.stringify(dataSend));
            //Xtermjs with attach dont print zero, so i force. Need to fix it :(
            if (e=="0"){
            term.write(e);
            }
        })
    })

}



</script>
<template>

    <Head title="Ssh" />

    <BreezeAuthenticatedLayout>
        <div v-if="isConnected" class="text-center">
            {{form.idconnection}}
        </div>
        <div class="ssh"></div>
        <!-- <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Ssh
            </h2>
        </template> -->
        <BreezeValidationErrors class="mb-4" />
        <div class="flex flex-col items-center min-h-screen pt-6 bg-gray-100 sm:justify-center sm:pt-0" v-if="!isConnected">
            <div class="w-full px-6 py-4 mt-6 overflow-hidden bg-white shadow-md sm:max-w-md sm:rounded-lg">
                <form @submit.prevent="submit">

                    <div>
                        <BreezeLabel for="idconnection" value="idconnection" />
                        <BreezeInput id="idconnection" type="text" class="block w-full mt-1" v-model="form.idconnection" required autofocus/>
                    </div>

                    <div class="flex items-center justify-center mt-4">

                        <BreezeButton class="ml-4" @click="authSsh">
                            连接
                        </BreezeButton>
                    </div>
                </form>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
<style>

@import "/node_modules/xterm/css/xterm.css";

</style>





