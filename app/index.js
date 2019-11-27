import Vue from 'vue';
import TodoApp from '@/TodoApp';
import store from "@/stores/todo-store";

Vue.config.productionTip = false

/* eslint-disable no-new */
window.onload = function () {
    new Vue({
        el: '#todo-app-container',
        store,
        components: { TodoApp }
    })
}