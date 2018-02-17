<style scoped>

    .strikethrough{
       text-decoration: line-through; 
    }

</style>

<template>
    <div>

        <span>
            <input type="checkbox" v-on:change="itemChecked">
        </span>

        <span id="title">
            {{ task.title }}
        </span>

    </div>
</template>


<script>

export default {
    components: {},
    mixins: [],
    data () {
      return {
        csrf: '',
        checked: this.task.completed
      }
    },
    mounted() {
        this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
    },
    props: [
        'task',
        'taskList'
    ],
    computed: {},
    methods: {
        itemChecked(){
            var self = this;
            console.log('Check box activated!');
            if (this.checked){
                var fd = new Object();
                fd._token = this.csrf;
                fd.list_id = this.taskList;
                fd.task_id = this.task.id;
                var fd_string = JSON.stringify(fd);
                axios.post('/gtasks/task/complete', fd_string)
                    .then(function(response){
                        self.$emit('refresh');
                        //console.log(response);
                    })
                    .catch(function(error){
                        console.log(error);
                    });
            }
            
        }
    }
};

</script>