<style scoped> </style>


<template>
    <div id="new_list_form">
        <p>Available lists:
            <ul>
                <li v-for="list in available_lists.items"> {{ list.title }} </li>
            </ul>
        </p>

        <form id="select_google_task_list" class="form-inline my-2 my-lg-0" v-on:submit.prevent="selectList" >
            <input type="hidden" name="_token" :value="csrf"></input>
            <input type="hidden" name="aspect_type_id" :value="aspectTypeId"></input>
            <input type="hidden" name="subject_id" :value="subjectId"></input>

            <div class="form-group">
                <label for="list_title">Title</label>
                <input type="text" id="list_title" name="list_title"></input>        
            </div>

            <div class="form-group">
                <label for="list_id">Use this task list</label>
                <select class="form-control" id="list_id" name="selected_list">
                    <option disabled value=""> -- Select a list -- </option>
                    <option v-for="list in available_lists.items" v-bind:value="list.id">{{ list.title }}</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-default">Select</button>
        </form>

        <p> - or - </p>

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
        task_list_name: '', 
        selected_list: '',
        list_options: ''
      }
    },
    mounted() {
        this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
        this.available_lists = this.fetchLists();
    },
    props: [
        'aspectType',
        'aspectTypeId',
        'title',
        'aspectNotes',
        'aspectSource',
        'subjectId'
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
        },
        selectList(){
            var self = this;
            var fd = $("#select_google_task_list").serialize();
            //fd['aspect_type_id'] = this.aspectTypeId;
            console.log(fd);
            //axios.post('/aspect/create', fd);
            
        }
    }
};
</script>

