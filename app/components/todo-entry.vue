<template>
    <tr class="test-list">
        <!-- I deliberated over removing the id and user_id fields, particularly the latter: it's a very small security concern.  There might be a good use case or reason for it being here, however, that I haven't been informed about. -->
        <td>{{ todoEntry.id }}</td>
        <td>{{ todoEntry.user_id }}</td>
        <td>
            <a v-if="showLink" :href="'/todo/' + todoEntry.id">
               {{ todoEntry.description }}
            </a>
            <template v-else>{{ todoEntry.description }}</template>
        </td>
        <td class="text-center">
            <!-- For some reason, the JSON is being parsed into just strings; with more time I would set up some proper data models to ingest the results of the queries and transform things like this 'complete' entry into a boolean or other appropriate data format -->
            <template v-if="todoEntry.completed === '1'">
                <!-- The request was only for a task to be completed, not the reverse.  This clearly states that something has been completed, and shows that it can't be touched again via the disabled attribute -->
                <button class="btn btn-xs btn-success" disabled title="Completed"><span class="glyphicon glyphicon-ok glyphicon-white"></span></button>
            </template>
            <template v-else>

                <button type="submit" class="btn btn-xs btn-default" :class="{'todo-entry-error': completeAnimate}" title="Complete task" v-on:click="completeTodo"><span class="glyphicon glyphicon-ok invisible"></span></button>
            </template>
        </td>
        <td class="text-center">
            <button type="submit" class="btn btn-xs btn-danger" :class="{'todo-entry-error': deleteAnimate}" v-on:click="deleteTodo"><span class="glyphicon glyphicon-remove"></span></button>
        </td>
    </tr>
</template>

<script>
import axios from "axios";

export default {
    name: 'TodoEntry',
    props: {
        todoEntry: {
            type: Object,
            required: true
        },
        showLink: {
            type: Boolean,
            required: true
        }
    },
    data () {
        return {
            completeAnimate: false,
            deleteAnimate: false
        }
    },
    methods: {
        //There's a distinct possibility that this app would run into some state issues, particularly if multiple users are able to log onto the same
        //account.  But taking care of that consideration would be a larger architectural undertaking.
        completeTodo: function(){
            axios.put("/todo/complete/" + this.todoEntry.id, {
                data: null,
                headers: { 'content-type': 'application/json' }
            })
            .then(response => {
                this.$store.commit("completeTodo", this.todoEntry.id);
            })
            .catch(err => {
                this.completeError();
            });
        },
        completeError: function() {
            //Did not have time for a proper error setup, preferably using toasts - at the least, this will allow users to get some reaction, but it
            //should be improved.
            this.completeAnimate = true;
            setTimeout(this.clearCompleteError, 1000);
        },
        clearCompleteError: function() {
            this.completeAnimate = false;
        },

        deleteTodo: function(){
            axios.delete("/todo/delete/" + this.todoEntry.id, {
                data: null,
                headers: { 'content-type': 'application/json' }
            })
            .then(response => {
                this.$store.commit("deleteTodo", this.todoEntry.id);
                this.$emit("todoDeleted")
            })
            .catch(err => {
                this.deleteError();
            });
        },
        deleteError: function() {
            this.deleteAnimate = true;
            setTimeout(this.clearDeleteError, 1000);
        },
        clearDeleteError: function() {
            this.deleteAnimate = false;
        }
    }
}
</script>

<style lang="css">
/* Animation adapted from https://css-tricks.com/snippets/css/shake-css-keyframe-animation/ */
.todo-entry-error {
    animation: todo-entry-error 0.5s cubic-bezier(.36,.07,.19,.97) both;
    transform: translate3d(0, 0, 0);
}

@keyframes todo-entry-error {
    10%, 90% {
        transform: translate3d(-1px, 0, 0);
    }
  
    20%, 80% {
        transform: translate3d(2px, 0, 0);
    }

    30%, 50%, 70% {
        transform: translate3d(-4px, 0, 0);
    }

    40%, 60% {
        transform: translate3d(4px, 0, 0);
    }
}
</style>