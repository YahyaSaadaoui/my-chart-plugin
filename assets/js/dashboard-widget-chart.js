import React, { useEffect, useState } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const DashboardWidgetChart = () => {
    const [chartData, setChartData] = useState([]);

    const fetchData = async (days) => {
        let url = `http://localhost:8080/wordpress/wp-json/DWC/v1/chart-data/${days}`;
        let response = await fetch(url);
        let data = await response.json();
        setChartData(data);
    };

    useEffect(() => {
        fetchData('7'); // Fetch data for the last 7 days by default
    }, []);

    return (
        <div>
            <h2>Dashboard Widget Chart</h2>
            <div className="dashboard-widget-chart-container" id="dashboard-widget-chart-container">
                <ResponsiveContainer>
                    <LineChart
                        width={500}
                        height={300}
                        data={chartData}
                        margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                    >
                        <XAxis dataKey="date" />
                        <YAxis />
                        <CartesianGrid strokeDasharray="3 3" />
                        <Tooltip />
                        <Legend />
                        <Line type="monotone" dataKey="value" stroke="#8884d8" activeDot={{ r: 8 }} />
                    </LineChart>
                </ResponsiveContainer>
            </div>
            <div className="dashboard-widget-chart-dropdown">
                <select onChange={(e) => fetchData(e.target.value)}>
                    <option value="7">Last 7 Days</option>
                    <option value="15">Last 15 Days</option>
                    <option value="30">Last Month</option>
                </select>
            </div>
        </div>
    );
};
function renderChart(data) {
    const chart = (
        <ResponsiveContainer width="100%" height={400}>
            <LineChart data={data}>
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="views" stroke="#8884d8" />
            </LineChart>
        </ResponsiveContainer>
    );
    ReactDOM.render(chart, document.getElementById('dashboard-widget-chart-container'));
}

function fetchChartData(days) {
    fetch('http://localhost:8080/wordpress/wp-json/DWC/v1/chart-data/' + days)
        .then(response => response.json())
        .then(data => {
            const chartData = data.map(item => ({
                name: item.date,
                views: item.views,
            }));
            renderChart(chartData);
        })
        .catch(error => console.error(error));
}
export default DashboardWidgetChart;
