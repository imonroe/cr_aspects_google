<style scoped>

    .strikethrough{
       text-decoration: line-through; 
    }

</style>

<template>
    <div>

        <span>
            <input type="checkbox" v-model="gTask.completed" v-on:click="itemChecked">
        </span>

        <span id="title">
            {{ gTask.title }}
        </span>

    </div>
</template>


<script>

export default {
    components: {},
    mixins: [],
    data () {
      return {
        gTask: ''
      }
    },
    mounted() {
        this.gTask = this.task;
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
            var fd = new Object();
            fd.list_id = this.taskList;
            fd.task_id = this.gTask.id;
            var fd_string = JSON.stringify(fd);
            this.$axios.post('/gtasks/task/complete', fd_string)
                .then(function(response){
                    self.$emit('refresh');
                    //console.log(response);
                })
                .catch(function(error){
                    console.log(error);
                });
        }
    }
};

</script>