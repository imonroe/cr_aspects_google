<style scoped>

    .centered {
        margin: auto;
    }

</style>

<template>

    <div >
        <datepicker class="centered" :value="currentDate" v-on:selected="dateChosen" :inline="true"></datepicker>
        
        <p> Some text goes here in my calendar widget.</p>

        <div>
            <p v-for="event in calendar.data.items" :key="event.id"> {{ event.summary }} - {{ formatDate(event.start) }} </p>
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
            formatDate(googleDate){
                // This is a weird thing, because google uses strange date objects in their JSON.
                var dateObject;
                if ( googleDate.dateTime !=null ){
                    dateObject = new Date(googleDate.dateTime);
                } else {
                    dateObject = new Date(googleDate.date);
                }
                return moment(dateObject).format('llll');
            },
            fetchCalendar(){
                var self=this;
                var fd = new Object();
                fd._token = this.csfr;
                fd.start_date = this.startDate;
                fd.end_date = this.endDate;
                fd.calendar_id = this.calendarId;
                //var fd_string = JSON.stringify(fd);
                var data = new Object();
                data.params = fd;
                axios.get('/gcal/calendar', data)
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
            dateChosen(date_object){
                console.log(date_object);
                this.startDate = date_object;
                var tomorrow = new Date();
                tomorrow.setDate(date_object.getDate() + 1);
                this.endDate = tomorrow;
                this.fetchCalendar();
            }
        }
    };

</script>