<style scoped> </style>


<template>
    <div id="new_list_form">
        <p>Available lists:
            <ul>
                <li v-for="list in available_lists.items"> {{ list.title }} </li>
            </ul>
        </p>
        <form id="new_google_task_list" class="form-inline my-2 my-lg-0" v-on:submit.prevent="createList">
            
            <input type="hidden" name="_token" :value="csrf">
            <input type="hidden" name="action" value="add_task_list">

            <div class="form-group">
                <label for="task_list_name">Create a new Task List</label>
                <input type="text" class="form-control" id="task_list_name" name="task_list_name" placeholder="New list title" v-model="task_list_name">
            </div>

            <button type="submit" class="btn btn-default">Create</button>
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
        available_lists: '',  
        task_list_name: ''
      }
    },
    mounted() {
        this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
        this.available_lists = this.fetchLists();
    },
    props: [
     
    ],
    computed: {
    
    },
    methods: {
        createList(){
          var self = this;
          var fd = $("#new_google_task_list").serialize();
          axios.post('/gtasks/new_list', fd)
            .then(function(response){
                //self.available_lists = response.data;
                if (response.data.kind == "tasks#taskList"){
                    console.log('Created list successfully.');
                }
                self.fetchLists();
                console.log(response.data);
            })
            .catch(function(error){
                console.log(error);
            });
        }, 
        fetchLists(){
            var self = this;
            axios.get('/gtasks/available_lists')
            .then(function(response){
                self.available_lists = response.data;
                console.log(response.data);
            })
            .catch(function(error){
                console.log(error);
            });
        }
    }
};
</script>

