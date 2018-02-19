<style scoped>

</style>

<template>

    <div>
        <datepicker :value="currentDate" v-on:selected="dateChosen" :inline="true"></datepicker>
        <p> Some text goes here in my calendar widget.</p>

        <div>
            <p v-for="event in calendar.data.items" :key="event.id"> {{ event.summary }} - {{ this.formatDate(event.start.dateTime) }} </p>
        </div>

    </div>

</template>


<script>
    import Datepicker from 'vuejs-datepicker';
    import moment from 'moment';
    export default {
        components: {
            Datepicker
        },
        mixins: [],
        data () {
            return {
                csrf: '',
                calendarId: '',
                calendar: {"data": {"items": []} },
                currentDate: '',
                startDate: '',
                endDate: ''
            }
        },
        created() {
            this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
            this.setCalendar();
            this.fetchCalendar();
        },
        props: [
            'aspectId',
            'aspectType',
            'aspectNotes',
            'aspectData',
            'aspectSource',
            'hidden',
            'subjectId',
            'settingsCalendarId',
            'title'
        ],
        computed: {

        },
        methods: {
            setCalendar(){
                this.calendarId = this.settingsCalendarId;
                this.currentDate = new Date();
                this.startDate = new Date();
                var tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                this.endDate = tomorrow;
            },
            formatDate(dateObject){
                return moment(dateObject).format('llll');
            },
            fetchCalendar(){
                var self=this;
                var fd = new Object();
                fd._token = this.csfr;
                fd.start_date = this.startDate;
                fd.end_date = this.endDate;
                fd.calendar_id = this.calendarId;
                var fd_string = JSON.stringify(fd);
                axios.get('/gcal/calendar', fd)
                    .then(function(response){
                        self.calendar = response;
                        console.log(response);
                    })
                    .catch(function(error){
                        console.log(error);
                    });

            },
            createCalendar(){

            },
            dateChosen(){

            }
        }
    };

</script>