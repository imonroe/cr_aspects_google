<style scoped> </style>


<template>
    <div id="new_list_form">
        
        <p>Available lists:
            <ul>
                <li v-for="list in available_lists.items"> {{ list.title }} </li>
            </ul>
        </p>

        <form v-if="this.doUpdate == false" id="select_google_task_list" class="form-inline my-2 my-lg-0"  >
        <!-- the create version. -->
            <input type="hidden" name="_token" :value="csrf"></input>
            <input type="hidden" name="aspect_type" :value="aspectType"></input>
            <input type="hidden" name="subject_id" :value="subjectId"></input>
            <input type="hidden" name="aspect_data" value=""></input>
            <input type="hidden" name="hidden" value="0"></input>
            <input type="hidden" name="aspect_source" value=""></input>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title"></input>        
            </div>

            <div class="form-group">
                <label for="settings_list_id">Use this task list</label>
                <select class="form-control" id="settings_list_id" name="settings_list_id">
                    <option disabled value=""> -- Select a list -- </option>
                    <option v-for="list in available_lists.items" v-bind:value="list.id">{{ list.title }}</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-default">Select</button>
        </form>

        '/aspect/' + self.aspectId + '/edit'

        <form v-else id="select_google_task_list" class="form-inline my-2 my-lg-0" method="POST" :action="actionPath" >
        <!-- the edit version. -->
            <input v-if="this.doUpdate == true" type="hidden" name="aspect_id" :value="aspectId"></input>
            <input type="hidden" name="_token" :value="csrf"></input>
            <input type="hidden" name="aspect_type" :value="aspectType"></input>
            <input type="hidden" name="subject_id" :value="subjectId"></input>
            <input type="hidden" name="aspect_data" value=""></input>
            <input type="hidden" name="hidden" value="0"></input>
            <input type="hidden" name="aspect_source" value=""></input>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" v-model="current_title" placeholder="Enter a title"></input>        
            </div>

            <div class="form-group">
                <label for="settings_list_id">Use this task list</label>
                <select class="form-control" id="settings_list_id" name="settings_list_id" v-model="selected_list">
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
        list_options: '',
        current_title: ''
      }
    },
    mounted() {
        this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
        this.available_lists = this.fetchLists();
        if ( this.doUpdate ){
            this.current_title = (this.title) ? this.title : '';
            this.selected_list = this.settingsListId;
        }
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
    computed: {
        doUpdate: function () {
            if (typeof this.aspectId === 'undefined' || this.aspectId === null) {
                return false;
            } else {
                return true;
            }
        },
        actionPath: function(){
            if ( this.doUpdate ){
                var action_path = '/aspect/' + this.aspectId + '/edit';
                return action_path;
            } else {
                return '/aspect/create';
            }
        }
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
            /**
             * fd = [
             *  '_token',
             *  'subject_id',
             *  'aspect_type',
             *  'title',
             *  'aspect_data',
             *  'hidden' => 0,
             *  'aspect_source',
             *  'settings_list_id',
             * ]
             * 
             */
            var self = this;
            var fd = $("#select_google_task_list").serialize();
            //fd['aspect_type_id'] = this.aspectTypeId;
            console.log(fd);
            axios.post('/aspect/create', fd);
            
        },
        editList(){
            var self = this;
            var fd = $("#select_google_task_list").serialize();
            //fd['aspect_type_id'] = this.aspectTypeId;
            console.log(fd);
            axios.post('/aspect/' + self.aspectId + '/edit', fd);
        }
    }
};
</script>

