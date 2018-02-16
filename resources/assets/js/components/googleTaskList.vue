<style scoped>

</style>



<template>

    <div>
        <p>Holding pattern.</p> 

        <ul>
            <li v-for="todo in taskList.items"> {{ todo.title }} </li>
        </ul>

        <form id="new_task" class="form-inline my-2 my-lg-0" v-on:submit.prevent="addNewTask">
            <input type="hidden" name="_token" :value="csrf">
            <input type="hidden" name="task_list" :value="settingsListId">

            <div class="form-group">
                <input type="text" class="form-control" id="new_task_title" name="new_task_title" placeholder="Add a new task" v-model="new_task_title" >
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>

    </div>

</template>



<script>
export default {
    components: {},
    mixins: [],
    data () {
      return {
        csrf: '',
        taskList: '',
        new_task_title: '',
      }
    },
    mounted() {
        this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
        this.fetchList();
    },
    props: [
        'aspectId',
        'aspectType',
        'aspectNotes',
        'aspectData',
        'aspectSource',
        'hidden',
        'subjectId',
        'settingsListId',
        'title'
    ],
    computed: {},
    methods: {
        fetchList(){
            console.log('trying to fetch list.');
            var self = this;
            axios.get('/gtasks/list/'+self.settingsListId)
            .then(function(response){
                self.taskList = response.data;
                console.log(response.data);
            })
            .catch(function(error){
                console.log(error);
            });
        }, 
        addNewTask(){
            var self = this;
            console.log('Trying to add new task: ' + self.new_task_title);
            var fd = $("#new_task").serialize();
            axios.post('/gtasks/task/add', fd)
            .then(function(response){
                self.fetchList();
            })
            .catch(function(error){
                console.log(error);
            });
        }
    }
};
</script>