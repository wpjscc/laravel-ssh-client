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

import { nextTick ,ref,watch} from 'vue'
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

const getTerm = ()=>{
    var term = new Terminal({
        fontFamily: '"Cascadia Code", Menlo, monospace',
        theme: baseTheme,
        cols: 80,
        rows: 24,
        cursorBlink: true
    });
    term.setOption('logLevel', 'debug');
    const searchAddon = new SearchAddon();
    term.loadAddon(searchAddon);

    fitAddon = new FitAddon();
    term.loadAddon(fitAddon);
    term.loadAddon(new WebLinksAddon());
    return term
}



const props = defineProps({
    idconnection: String,
    ws: String,
});
const isConnected = ref(0)
const refreshState = ref()
refreshState.value = true
const form = ref({
    idconnection: props.idconnection,
    server: '127.0.0.1',
    port: '22',
    user: 'jcc',
    password: '',
    type: '1',
    certificate:''
});

const idconnections = ref([])

// idconnections.value.push(props.idconnection)
const getIdconnection = async ()=>{
    var res = await axios.get(route('idconnection'))
    return res.data.idconnection
}
var wSocket;
var term;
var wSocket;
var fitAddon;

const connectWbsocket = async ()=>{
     term = getTerm()

    wSocket = new WebSocket(props.ws);
    const attachAddon = new AttachAddon(wSocket);
    term.loadAddon(attachAddon);


    wSocket.onopen = function (event) {
        isConnected.value=true

        intervalId = window.setInterval(function(){
            wSocket.send(JSON.stringify({"refresh":""}));
        }, 700);
    };
    wSocket.onerror = function (event){
            console.error("Socket Error",event);
            isConnected.value=false
            refreshState.value = false

            window.clearInterval(intervalId);
        };
    wSocket.onclose = function (event){
        console.error("Socket onclose",event);
        isConnected.value=false
        refreshState.value=false
        window.clearInterval(intervalId);
    };
}

const authSsh = async ()=>{

    await connectWbsocket()

    var idconnection = await getIdconnection()
    idconnections.value.push(idconnection)

    console.log("Socket Open");
    var newObject = Object.assign({}, form.value)
    newObject.idconnection = idconnection

    wSocket.send(JSON.stringify({
        auth: newObject
    }));

     nextTick(()=>{
        term.open(document.querySelector(`.c${idconnection}`));
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
        <!-- <div v-if="isConnected" class="text-center">
            {{form.idconnection}}
        </div> -->
        <div v-for="(item,key) in idconnections"  :key="item">
           <div class="text-center">
                {{item}}
           </div>
           <div :class="'c'+item">

           </div>
        </div>

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
                        <BreezeLabel for="server" value="server" />
                        <BreezeInput id="server" type="text" class="block w-full mt-1" v-model="form.server" required autofocus autocomplete="server" />
                    </div>
                    <div>
                        <BreezeLabel for="port" value="port" />
                        <BreezeInput id="port" type="number" class="block w-full mt-1" v-model="form.port" required autofocus autocomplete="port" />
                    </div>
                    <div>
                        <BreezeLabel for="user" value="user" />
                        <BreezeInput id="user" type="text" class="block w-full mt-1" v-model="form.user" required autofocus autocomplete="user" />
                    </div>
                    <div>
                        <BreezeLabel for="type" value="type" />
                         <input type="radio" value="1" v-model="form.type"
           class="text-indigo-600 border-gray-300 rounded shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"> 密码
                         <input type="radio" value="2" v-model="form.type"
           class="text-indigo-600 border-gray-300 rounded shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"> 证书

                    </div>
                     <div class="mt-4" v-if="form.type==1">
                        <BreezeLabel for="password" value="Password" />
                        <BreezeInput id="password" type="password" class="block w-full mt-1" v-model="form.password"  autocomplete="current-password" />
                    </div>
                     <div class="mt-4" v-if="form.type==2">
                        <BreezeLabel for="certificate" value="certificate" />
                        <textarea v-model="form.certificate" id="" cols="40" rows="10"></textarea>
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





