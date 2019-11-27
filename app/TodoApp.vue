<template>
    <table class="table table-striped todo-table">
        <!-- Previously, it seems Boostrap was compensating for the display of the table; as the app now loads the todos after page load,
            it seems that we lost that compensation.  -->
        <thead>
            <tr>
                <th>#</th><th>User</th><th>Description</th><th class="text-center"><span class="glyphicon glyphicon-ok"></span></th><th></th>
            </tr>
        </thead>
        <!-- Cheating a little bit, here.  HTML rules permit multiple tbody tag, and the transition-group element requires some tag to render,
            but this is technically incorrect HTML -->
        <transition-group appear name="fade" tag="tbody">
            <todo-entry v-for="todo in todosList" :todo-entry="todo" :show-link="showLink" :key="todo.id" v-on:todoDeleted="todoDeleted"></todo-entry>
        </transition-group>
        <tbody v-if="queryError">
            <tr>
                <!-- This should really be in a configuration somewhere, especially for easy manipulation for the build scripts -->
                <td colspan="5">There was a problem retrieving your data.  Please try again later.</td>
            </tr>
        </tbody>
        <tfoot v-if="!target">
            <tr>
                <td colspan="4">
                    <form ref="addTodo" method="post" action="/todo/add">
                        <input type="textbox" name="description" class="small-6 small-center" placeholder="Description...">
                    </form>
                </td>
                <td>
                    <button type="submit" class="btn btn-sm btn-primary" v-on:click="addTodo">Add</button>
                </td>
            </tr>
        </tfoot>
    </table>
</template>

<script>
import TodoEntry from "@/components/todo-entry";
import axios from "axios";

export default {
    name: 'TodoApp',
    data () {
        return {
            queryError: false
        }
    },
    props: {
        //Determines whether to show the descriptions as a link or not.  Keeping it seperate from target below gives us more granular control.
        showLink: {
            type: Boolean,
            required: true
        },
        //By letting the TodoApp target a single ID, we can eliminate having a seperate view when looking at a single Todo.
        target: {
            type: String,
            required: false
        }
    },
    computed: {
        todosList() {
            return this.$store.state.todos;
        } 
    },
    mounted() {
        //Ideally all the Axios code should be moved to it's own service, but I wasn't sure the best way to properly handle the interaction between
        //it, Vuex, and Vue properly within the timeframe.
        axios.get("/todo" + (this.target ? "/" + this.target : ""), {
                data: null,
                headers: { 'content-type': 'application/json' }
            })
            .then(response => {
                var data = response.data;
                if(this.target){
                    //Ideally this should be an array when it comes down to the frontend
                    data = [data];
                }
                this.$store.commit('setTodos', data);
            })
            .catch(err => {
                this.queryError = true;
            });
    },
    components: {
        TodoEntry
    },
    methods: {
        addTodo: function(){
            //The requirement didn't mention updating the Add method.  It could easily follow the pattern used in for the TodoEntries, but making 
            //changes for the sake of making changes tends to be poor practice.
            this.$refs.addTodo.submit()
        },
        todoDeleted: function(){
            //If the user just deleted the only entry we're displaying, may as well kick them back to the main list.
            if(this.target){
                window.location.href = "/todo";
            }
        }
    }
}
</script>

<style lang="css">

/* Todo Table spacing rules */
.todo-table {
    width: 100%;
}

/* Todo ID column */
.todo-table th:nth-child(1), .todo-table td:nth-child(1){
    width: 10%;
}

/* User ID column */
.todo-table th:nth-child(2), .todo-table td:nth-child(2){
    width: 15%;
}

/* Description column */
.todo-table th:nth-child(3), .todo-table td:nth-child(3){
    word-break: break-all;
    word-wrap: break-word;
    width: 55%;
}

/* Completed column */
.todo-table th:nth-child(4), .todo-table td:nth-child(4){
    width: 10%;
}

/* Delete column */
.todo-table th:nth-child(4), .todo-table td:nth-child(4){
    width: 10%;
}

/* Todo Entry list fade animation, from the Vue animation examples: https://vuejs.org/v2/guide/transitions.html */
.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}

.fade-enter, .fade-leave-to {
    opacity: 0;
}
</style>