<style scoped>

</style>

<template>

    <div>
         
        <div v-for="todo in taskList.items" :key="todo.id"> 
            <google-single-task :task="todo" :task-list="settingsListId" v-on:refresh="fetchList"></google-single-task> 
        </div>

        <form :id="'new_task_'+settingsListId" class="form-inline my-2 my-lg-0" v-on:submit.prevent="addNewTask">
            <div class="form-group">
                <input type="text" class="form-control" id="new_task_title" name="new_task_title" placeholder="Add a new task" v-model="new_task_title" >
                <input type="hidden" name="task_list" :value="settingsListId">
                <input type="hidden" name="_token" :value="csrfToken"></input>
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
        taskList: '',
        new_task_title: '',
      }
    },
    mounted() {
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
            this.$axios.get('/gtasks/list/'+self.settingsListId)
            .then(function(response){
                self.taskList = response.data;
                console.log(response.data);
                self.$nextTick(function(){
                    self.$rejigger(); 
                });
            })
            .catch(function(error){
                console.log(error);
            });
        }, 
        addNewTask(){
            var self = this;
            console.log('Trying to add new task: ' + self.new_task_title);
            var fd = self.$jquery("#new_task_"+self.settingsListId).serialize();
            this.$axios.post('/gtasks/task/add', fd)
            .then(function(response){
                self.fetchList();
                self.new_task_title = '';
            })
            .catch(function(error){
                console.log(error);
            });
        }
    }
};
</script>