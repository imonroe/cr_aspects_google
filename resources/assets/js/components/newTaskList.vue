<style scoped>

</style>


<template>

    <form id="new_google_task_list" class="form-inline my-2 my-lg-0">
        <input type="hidden" name="_token" :value="csrf">
        <el-input placeholder="Please input" v-model="input">
            <el-button type="primary" @click="createList" slot="append">Create</el-button>
        </el-input>
        
    </form>

</template>


<script>
export default {
    components: {},
    mixins: [],
    data () {
      return {
        value: ''
      }
    },
    mounted() {},
    props: [
     
    ],
    computed: {
    
    },
    methods: {
        createList(){
          var form = document.getElementById("basic_search_form");
          var fd = serialize(form);
          axios.post('/gtasks/new_list', fd)
            .then(function(response){
                console.log(response);
            })
            .catch(function(error){
                console.log(error);
            });
        }
    }
};
</script>

