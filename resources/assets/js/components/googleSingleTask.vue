<style scoped>

</style>

<template>
    <div id="item_x">

        <span>
            <input type="checkbox" id="checkbox" v-model="checked">
        </span>

        <span>
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
        taskList: '',
        task: '',
        checked: ''
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
        
    }
};

</script>