<style scoped>

</style>



<template>

    <div>
          <p>Holding pattern.<p/>  
    </div>

</template>



<script>
export default {
    components: {},
    mixins: [],
    data () {
      return {
        csrf: '',
        taskList: ''
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
            var self = this;
            axios.get('/gtasks/list/'+self.settingsListId)
            .then(function(response){
                self.taskList = response.data;
                console.log(response.data);
            })
            .catch(function(error){
                console.log(error);
            });
        }
    }
};
</script>