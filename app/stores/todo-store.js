import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

const state = {
    todos: []
};

const getters = {
    
};

const actions = {

};

const mutations = {
    setTodos(state, todos) {
        state.todos = todos;
    },
    completeTodo(state, id){
        //I deliberated between using findIndex or having a dictionary setup in a object; staying with an array makes display a lot easier.
        var index = state.todos.findIndex(obj => obj.id === id);
        state.todos[index].completed = "1";
    },
    deleteTodo(state, id){
        var index = state.todos.findIndex(obj => obj.id === id);
        state.todos.splice(index, 1);
    }
};

export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations
})